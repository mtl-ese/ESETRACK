<?php

namespace App\Http\Controllers;

use App\Models\RecoveryStoreRequisition;
use App\Models\StoreRequisitionDestinationItem;
use App\Models\StoreItem;
use App\Models\RecoveryStore;
use App\Models\RecoveryStoreSerialNumber;
use App\Models\ItemSerialNumber;
use App\Models\StoreRequisitionSerialNumber;
use Illuminate\Support\Facades\DB;
use App\Models\RecoveryStoreRequisitionItem;
use App\Models\RecoveryStoreRequisitionItemSerialNumber;
use App\Models\StoreRequisition;
use App\Models\StoreReturn;
use App\Models\StoreReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class RecoveryStoreRequisitionController extends Controller
{
    private function extractSerialValues($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->extractSerialValues($decoded);
            }
            $trimmed = trim($value);
            return $trimmed === '' ? [] : [$trimmed];
        }
        if (is_array($value)) {
            return collect(Arr::flatten($value))->map(function ($serial) {
                return is_string($serial) ? trim($serial) : (string) $serial;
            })->filter(function ($serial) {
                return $serial !== '';
            })->values()->all();
        }
        if ($value === null) {
            return [];
        }
        return [(string) $value];
    }

    public function index()
    {
        $recoveries = RecoveryStoreRequisition::latest()->with(['creator', 'destinationLink.destination'])->get();

        return view('recovery.index', [
            'recoveries' => $recoveries
        ]);
    }
    public function create()
    {
        // Load requisitions with items and their destinations
        $requisitions = StoreRequisition::with('items.destinationItems.link.destination')->get()
            ->filter(function ($requisition) {
                foreach ($requisition->items as $item) {
                    foreach ($item->destinationItems as $destItem) {
                        // Total recovered quantity for this destination item
                        $recoveredQty = RecoveryStoreRequisitionItem::where('store_item_id', $item->id)
                            ->where('destination_link_id', $destItem->destination_link_id)
                            ->whereHas('recovery_store_requisition', fn($q) => $q->where('store_requisition_id', $requisition->requisition_id))
                            ->sum('quantity');

                        if ($recoveredQty < $destItem->quantity) {
                            return true; // Keep this requisition if any destination still needs recovery
                        }
                    }
                }
                return false; // Remove if fully recovered
            })
            ->values();

        // Prepare datalist labels with unique clients only
        $requisitionsForDatalist = $requisitions->map(function ($req) {
            $clients = $req->items
                ->flatMap(fn($item) => $item->destinationItems)
                ->map(fn($destItem) => optional($destItem->link->destination)->client ?? 'N/A')
                ->unique()
                ->implode(', ');

            return [
                'requisition_id' => $req->requisition_id,
                'clients_label' => $clients ?: 'N/A',
            ];
        });

        return view('recovery.create', [
            'requisitions' => $requisitionsForDatalist,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'requisition_id' => 'required|exists:store_requisitions,requisition_id',
            'approved_by' => 'required|string',
            'recovery_date' => 'required|date|before_or_equal:today',
            'quantities' => 'required|array',
            'quantities.*' => 'nullable|integer|min:1',
            'serials' => 'nullable|array',
            'serials.*' => 'array',
            'serials.*.*' => 'string'
        ]);

        // Force serial keys to integers to match material IDs
        $serialSelections = collect($validated['serials'] ?? [])
            ->mapWithKeys(fn($value, $key) => [(int) $key => $value])
            ->toArray();

        // Force quantity keys to integers and filter out zero/empty values
        $quantities = collect($validated['quantities'])
            ->mapWithKeys(fn($value, $key) => [(int) $key => (int) $value])
            ->filter(fn($q) => $q > 0)
            ->toArray();

        if (empty($quantities)) {
            return redirect()->back()
                ->with('error', 'Please enter at least one recovered quantity')
                ->withInput();
        }

        try {
            DB::transaction(function () use ($validated, $quantities, $serialSelections) {

                $storeRequisition = StoreRequisition::where('requisition_id', $validated['requisition_id'])->firstOrFail();

                $requisition = RecoveryStoreRequisition::create([
                    'store_requisition_id' => $validated['requisition_id'],
                    'was_created_by' => $storeRequisition->created_by,
                    'created_by' => Auth::id(),
                    'was_approved_by' => $storeRequisition->approved_by,
                    'approved_by' => $validated['approved_by'],
                    'recovered_on' => $validated['recovery_date'],
                ]);

                foreach ($quantities as $itemId => $recoveredQty) {
                    // Cast to int immediately
                    $itemId = (int) $itemId;
                    $recoveredQty = (int) $recoveredQty;

                    $destinationItem = StoreRequisitionDestinationItem::findOrFail($itemId);
                    $storeItem = $destinationItem->item;
                    $destinationLinkId = $destinationItem->destination_link_id;

                    // Serial numbers submitted for this item
                    $selectedSerials = $serialSelections[$itemId] ?? [];
                    if (!is_array($selectedSerials)) {
                        $selectedSerials = [$selectedSerials];
                    }

                    $selectedSerials = array_values(array_unique(array_map(
                        'strval',
                        array_filter($selectedSerials, fn($s) => $s !== null && $s !== '')
                    )));

                    // Available serials for this item + requisition
                    $availableSerials = StoreRequisitionSerialNumber::where('store_requisition_id', $validated['requisition_id'])
                        ->whereHas('itemSerialNumber', fn($q) => $q->where('item_name', $storeItem->item_name))
                        ->with('itemSerialNumber')
                        ->get()
                        ->pluck('itemSerialNumber.serial_number')
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray();

                    // Remove already used serials for THIS specific destination
                    $usedSerials = RecoveryStoreRequisitionItemSerialNumber::whereHas('item', function ($q) use ($validated, $storeItem, $destinationLinkId) {
                        $q->where('item_name', $storeItem->item_name)
                            ->where('destination_link_id', $destinationLinkId)
                            ->whereHas('recovery_store_requisition', fn($inner) => $inner->where('store_requisition_id', $validated['requisition_id']));
                    })->get()
                        ->pluck('serial_number')
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray();

                    $availableSerials = array_values(array_diff($availableSerials, $usedSerials));

                    // Check quantity vs available serials
                    if (!empty($availableSerials)) {
                        if ($recoveredQty !== count($selectedSerials)) {
                            throw new \Exception('Select exactly ' . $recoveredQty . ' serial number(s) for ' . $storeItem->item_name . '.');
                        }
                        $invalidSerials = array_diff($selectedSerials, $availableSerials);
                        if (!empty($invalidSerials)) {
                            throw new \Exception('Invalid serial numbers selected for ' . $storeItem->item_name . '.');
                        }
                    } elseif (!empty($selectedSerials)) {
                        throw new \Exception('Serial numbers not available for ' . $storeItem->item_name . '.');
                    }

                    // Create recovery item
                    $requisitionItem = RecoveryStoreRequisitionItem::create([
                        'recovery_requisition_id' => $requisition->recovery_requisition_id,
                        'store_item_id' => $storeItem->id, // Use id, not store_item_id
                        'destination_link_id' => $destinationLinkId,
                        'item_name' => $storeItem->item_name,
                        'quantity' => $recoveredQty,
                        'balance' => $destinationItem->quantity - $recoveredQty,
                    ]);

                    // Save serial numbers
                    foreach ($selectedSerials as $serial) {
                        RecoveryStoreRequisitionItemSerialNumber::create([
                            'item_id' => $requisitionItem->id,
                            'serial_number' => $serial,
                            'returned' => false
                        ]);
                    }

                    // Update recovery store
                    $recoveryStore = RecoveryStore::firstOrNew(['item_name' => $storeItem->item_name]);
                    $recoveryStore->quantity = ((int) $recoveryStore->quantity) + $recoveredQty;
                    $recoveryStore->save();

                    foreach ($selectedSerials as $serial) {
                        RecoveryStoreSerialNumber::create([
                            'recovery_store_id' => $recoveryStore->id,
                            'serial_numbers' => $serial,
                        ]);
                    }
                }

            });

            return redirect()->route('recovery.index')
                ->with('success', 'Recovery Store Requisition created successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        $recoveries = RecoveryStoreRequisition::where('recovery_requisition_id', 'LIKE', "%{$query}%")
            ->orWhere('client_name', 'LIKE', "%{$query}%")
            ->with('creator')
            ->get();

        if ($recoveries->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No records found')
                ->withInput();
        }

        return view('recovery.search', [
            'recoveries' => $recoveries,
            'query' => $query
        ]);
    }
    public function destroy($requisition_id)
    {

        $recovery = RecoveryStoreRequisition::with('items.serial_numbers')->where('recovery_requisition_id', $requisition_id)->first();


        //return the item and its serial numbers if present
        if ($recovery->items != null) {
            foreach ($recovery->items as $item) {

                //get item in recovery stores
                $recovereditem = RecoveryStore::where('item_name', $item->item_name)->first();

                //calculate balance
                $balance = max($recovereditem->quantity - $item->quantity, 0);

                //assign balance
                $recovereditem->quantity = $balance;


                if ($item->serial_numbers != null) {
                    foreach ($item->serial_numbers as $serial_number) {
                        RecoveryStoreSerialNumber::create([
                            'recovery_store_id' => $recovereditem->id,
                            'serial_numbers' => $serial_number->serial_number
                        ]);
                    }
                }

                $recovereditem->save();
                if ($recovereditem->quantity === 0) {
                    $recovereditem->delete();
                }

            }
        }


        //delete the recovery
        $recovery->delete();

        return redirect()
            ->route('recovery.index')
            ->with('success', 'Recovery Store Requisition ' . $requisition_id . ' deleted successfully.');
    }

    public function loadMaterials($requisitionId)
    {
        try {
            $requisition = StoreRequisition::with('items.destinationItems.link.destination')
                ->where('requisition_id', $requisitionId)
                ->firstOrFail();

            $groupedMaterials = $requisition->items->flatMap(function ($item) use ($requisitionId) {
                return $item->destinationItems->map(function ($destItem) use ($item, $requisitionId) {

                    // Correctly sum already recovered quantity for THIS destination
                    $alreadyRecovered = RecoveryStoreRequisitionItem::where('store_item_id', $item->id) // Use id, not store_item_id
                        ->where('destination_link_id', $destItem->destination_link_id)
                        ->whereHas('recovery_store_requisition', fn($q) => $q->where('store_requisition_id', $requisitionId))
                        ->sum('quantity');

                    // Serial numbers available for THIS item + requisition
                    $allSerials = StoreRequisitionSerialNumber::where('store_requisition_id', $requisitionId)
                        ->whereHas('itemSerialNumber', fn($q) => $q->where('item_name', $item->item_name))
                        ->with('itemSerialNumber')
                        ->get()
                        ->pluck('itemSerialNumber.serial_number')
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray();

                    // Remove already recovered serials for this destination
                    $usedSerials = RecoveryStoreRequisitionItemSerialNumber::whereHas(
                        'item',
                        fn($q) =>
                        $q->where('store_item_id', $item->id) // Use id, not store_item_id
                            ->where('destination_link_id', $destItem->destination_link_id)
                    )->pluck('serial_number')->toArray();

                    $availableSerials = array_values(array_diff($allSerials, $usedSerials));

                    return [
                        'id' => $destItem->id,
                        'item_name' => $item->item_name,
                        'quantity' => $destItem->quantity,
                        'recovered' => $alreadyRecovered,
                        'balance' => $destItem->quantity - $alreadyRecovered,
                        'serial_numbers' => $availableSerials,
                        'destination_client' => $destItem->link->destination->client ?? 'N/A',
                        'destination_location' => $destItem->link->destination->location ?? 'N/A',
                    ];
                });
            });

            return response()->json([
                'success' => true,
                'materials' => $groupedMaterials
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }




    public function editForm($requisition_id)
    {
        $recovery = RecoveryStoreRequisition::with([
            'items.serial_numbers',
            'store_requisition.items.destinationItems.link.destination'
        ])
            ->where('recovery_requisition_id', $requisition_id)
            ->first();

        if (!$recovery) {
            return redirect()->back()->with('error', 'Recovery store requisition not found');
        }

        // Get all destination items for this requisition
        $allDestinationItems = $recovery->store_requisition->items
            ->flatMap(fn($item) => $item->destinationItems->map(function ($destItem) use ($item) {
                return [
                    'id' => $destItem->id,
                    'store_item_id' => $item->id, // Use id, not store_item_id
                    'item_name' => $item->item_name,
                    'quantity' => $destItem->quantity,
                    'destination_link_id' => $destItem->destination_link_id,
                    'destination' => $destItem->link->destination
                ];
            }));

        // Get available serials for this requisition
        $availableSerials = $this->getAvailableSerialsByItem($recovery->store_requisition_id);

        $items = $allDestinationItems->map(function ($destItem) use ($recovery, $availableSerials) {
            // Find existing recovery item for this destination (match by store_item_id and destination_link_id)
            $existingRecoveryItem = $recovery->items
                ->where('store_item_id', $destItem['store_item_id'])
                ->firstWhere('destination_link_id', $destItem['destination_link_id']);

            // Calculate already recovered for this specific destination (including ALL recoveries)
            // Use item_name as fallback since store_item_id might be inconsistent in old data
            $alreadyRecovered = RecoveryStoreRequisitionItem::where('item_name', $destItem['item_name'])
                ->where('destination_link_id', $destItem['destination_link_id'])
                ->whereHas('recovery_store_requisition', function ($q) use ($recovery) {
                    $q->where('store_requisition_id', $recovery->store_requisition_id);
                })
                ->sum('quantity');

            $currentQuantity = $existingRecoveryItem ? $existingRecoveryItem->quantity : 0;

            // Subtract current quantity to get what was recovered by OTHER recoveries
            $alreadyRecoveredByOthers = max(0, $alreadyRecovered - $currentQuantity);

            // Calculate balance: what's left to recover
            $balance = max(0, $destItem['quantity'] - $alreadyRecovered);

            // Max allowed: current quantity + remaining balance
            $maxAllowed = $currentQuantity + $balance;

            // Get current serials for this recovery item
            $currentSerials = $existingRecoveryItem
                ? $existingRecoveryItem->serial_numbers
                    ->flatMap(fn($serial) => $this->extractSerialValues($serial->serial_number))
                    ->filter(fn($serial) => $serial !== '')
                    ->unique()
                    ->values()
                    ->all()
                : [];

            return [
                'id' => $existingRecoveryItem ? $existingRecoveryItem->id : null,
                'destination_item_id' => $destItem['id'],
                'store_item_id' => $destItem['store_item_id'],
                'item_name' => $destItem['item_name'],
                'issued_quantity' => $destItem['quantity'],
                'already_recovered' => $alreadyRecovered,
                'quantity' => $currentQuantity,
                'balance' => $balance,
                'max_quantity' => $maxAllowed,
                'serial_numbers' => $currentSerials,
                'destination' => $destItem['destination'] ? [
                    'client' => $destItem['destination']->client,
                    'location' => $destItem['destination']->location
                ] : null
            ];
        })->filter(function ($item) {
            // Show items that either have current quantity > 0 OR have balance > 0
            return $item['quantity'] > 0 || $item['balance'] > 0;
        });

        return view('recovery.edit', [
            'recovery' => $recovery,
            'items' => $items,
            'availableSerials' => $availableSerials,
        ]);
    }
    public function updateAll(Request $request, $recoveryId)
    {
        $recovery = RecoveryStoreRequisition::with([
            'items.serial_numbers',
            'store_requisition.items.destinationItems.link.destination'
        ])
            ->where('recovery_requisition_id', $recoveryId)
            ->first();

        if (!$recovery) {
            return redirect()->back()->with('error', 'Recovery store requisition not found');
        }

        $validated = $request->validate([
            'approved_by' => ['required', 'string'],
            'recovery_date' => ['required', 'date', 'before_or_equal:today'],
            'items' => ['nullable', 'array'],
            'items.*.quantity' => ['nullable', 'integer', 'min:0'],
            'items.*.destination_item_id' => ['nullable', 'integer'],
            'items.*.store_item_id' => ['nullable', 'integer'],
            'serials' => ['nullable', 'array'],
            'serials.*.*' => ['string'],
        ]);

        // No-change detection: compare main fields and items layout
        $hasChanges = false;

        $existingDate = $recovery->recovered_on ? date('Y-m-d', strtotime($recovery->recovered_on)) : null;
        if (($recovery->approved_by ?? null) !== $validated['approved_by'] || $existingDate !== $validated['recovery_date']) {
            $hasChanges = true;
        }

        // Compare submitted items structure with existing recovery items
        if (!$hasChanges) {
            $submittedItems = $validated['items'] ?? [];

            // Build existing entries keyed by destination_item_id or store item
            $existingEntries = [];
            foreach ($recovery->items as $item) {
                $serials = $item->serial_numbers->map(fn($s) => (string) $s->serial_number)->filter()->unique()->values()->all();
                $existingEntries[] = [
                    'destination_item_id' => $item->id,
                    'store_item_id' => $item->store_item_id,
                    'quantity' => (int) $item->quantity,
                    'serials' => $serials,
                ];
            }

            $submittedEntries = [];
            foreach ($submittedItems as $key => $it) {
                $submittedEntries[] = [
                    'destination_item_id' => $it['destination_item_id'] ?? null,
                    'store_item_id' => $it['store_item_id'] ?? null,
                    'quantity' => (int) ($it['quantity'] ?? 0),
                    'serials' => array_values(array_map('strval', (array) ($request->input('serials.' . $key, []) ?? []))),
                ];
            }

            $normalize = function ($arr) {
                foreach ($arr as &$e)
                    sort($e['serials']);
                usort($arr, fn($a, $b) => (($a['destination_item_id'] ?? 0) <=> ($b['destination_item_id'] ?? 0)));
                return $arr;
            };

            if (json_encode($normalize($existingEntries)) !== json_encode($normalize($submittedEntries))) {
                $hasChanges = true;
            }
        }

        if (!$hasChanges) {
            return redirect()->back()->with('error', 'Nothing was changed. Please adjust at least one return quantity or serial selection.')->withInput();
        }

        try {
            DB::transaction(function () use ($validated, $recovery) {
                // Update recovery main info
                $recovery->update([
                    'approved_by' => $validated['approved_by'],
                    'recovered_on' => $validated['recovery_date'],
                ]);

                $itemsInput = collect($validated['items'] ?? []);
                $serialSelections = collect($validated['serials'] ?? []);
                $existingItems = $recovery->items->keyBy('id');

                // Track inventory changes for RecoveryStore
                $inventoryChanges = [];

                foreach ($itemsInput as $itemKey => $data) {
                    $newQuantity = (int) ($data['quantity'] ?? 0);
                    $selectedSerials = $serialSelections[$itemKey] ?? [];

                    // Normalize selected serials
                    if (!is_array($selectedSerials)) {
                        $selectedSerials = [$selectedSerials];
                    }
                    $selectedSerials = array_values(array_unique(array_map(
                        'strval',
                        array_filter($selectedSerials, fn($s) => $s !== null && $s !== '')
                    )));

                    if (str_starts_with($itemKey, 'new_')) {
                        // Handle new recovery items
                        if ($newQuantity > 0) {
                            $destinationItemId = $data['destination_item_id'];
                            $storeItemId = $data['store_item_id'];

                            $destinationItem = StoreRequisitionDestinationItem::findOrFail($destinationItemId);
                            $storeItem = StoreItem::findOrFail($storeItemId);

                            // Get available serials for this item
                            $availableSerials = StoreRequisitionSerialNumber::where('store_requisition_id', $recovery->store_requisition_id)
                                ->whereHas('itemSerialNumber', fn($q) => $q->where('item_name', $storeItem->item_name))
                                ->with('itemSerialNumber')
                                ->get()
                                ->pluck('itemSerialNumber.serial_number')
                                ->filter()
                                ->unique()
                                ->values()
                                ->toArray();

                            // Remove already used serials (excluding current recovery if editing)
                            $usedSerials = RecoveryStoreRequisitionItemSerialNumber::whereHas('item', function ($q) use ($recovery, $storeItem) {
                                $q->where('item_name', $storeItem->item_name)
                                    ->whereHas('recovery_store_requisition', fn($inner) => $inner->where('store_requisition_id', $recovery->store_requisition_id));
                            })->get()
                                ->pluck('serial_number')
                                ->filter()
                                ->unique()
                                ->values()
                                ->toArray();

                            $availableSerials = array_values(array_diff($availableSerials, $usedSerials));

                            // Validate serials
                            if (!empty($availableSerials)) {
                                if ($newQuantity !== count($selectedSerials)) {
                                    throw new \Exception('Select exactly ' . $newQuantity . ' serial number(s) for ' . $storeItem->item_name . '.');
                                }
                                $invalidSerials = array_diff($selectedSerials, $availableSerials);
                                if (!empty($invalidSerials)) {
                                    throw new \Exception('Invalid serial numbers selected for ' . $storeItem->item_name . '.');
                                }
                            } elseif (!empty($selectedSerials)) {
                                throw new \Exception('Serial numbers not available for ' . $storeItem->item_name . '.');
                            }

                            $newRecoveryItem = RecoveryStoreRequisitionItem::create([
                                'recovery_requisition_id' => $recovery->recovery_requisition_id,
                                'store_item_id' => $storeItemId,
                                'destination_link_id' => $destinationItem->destination_link_id,
                                'item_name' => $storeItem->item_name,
                                'quantity' => $newQuantity,
                                'balance' => $destinationItem->quantity - $newQuantity,
                            ]);

                            // Track inventory change for new item
                            if (!isset($inventoryChanges[$storeItem->item_name])) {
                                $inventoryChanges[$storeItem->item_name] = ['quantity' => 0, 'serials' => []];
                            }
                            $inventoryChanges[$storeItem->item_name]['quantity'] += $newQuantity;
                            $inventoryChanges[$storeItem->item_name]['serials'] = array_merge(
                                $inventoryChanges[$storeItem->item_name]['serials'],
                                $selectedSerials
                            );

                            // Add serials for new item
                            foreach ($selectedSerials as $serial) {
                                RecoveryStoreRequisitionItemSerialNumber::create([
                                    'item_id' => $newRecoveryItem->id,
                                    'serial_number' => $serial,
                                    'returned' => false,
                                ]);
                            }
                        }
                    } else {
                        // Handle existing recovery items
                        $itemModel = $existingItems[$itemKey] ?? null;
                        if (!$itemModel)
                            continue;

                        $oldQuantity = $itemModel->quantity;
                        $quantityDifference = $newQuantity - $oldQuantity;

                        // Get available serials for this item
                        $availableSerials = StoreRequisitionSerialNumber::where('store_requisition_id', $recovery->store_requisition_id)
                            ->whereHas('itemSerialNumber', fn($q) => $q->where('item_name', $itemModel->item_name))
                            ->with('itemSerialNumber')
                            ->get()
                            ->pluck('itemSerialNumber.serial_number')
                            ->filter()
                            ->unique()
                            ->values()
                            ->toArray();

                        // Remove already used serials (excluding current item's serials)
                        $usedSerials = RecoveryStoreRequisitionItemSerialNumber::whereHas('item', function ($q) use ($recovery, $itemModel, $itemKey) {
                            $q->where('item_name', $itemModel->item_name)
                                ->where('id', '!=', $itemKey) // Exclude current item
                                ->whereHas('recovery_store_requisition', fn($inner) => $inner->where('store_requisition_id', $recovery->store_requisition_id));
                        })->get()
                            ->pluck('serial_number')
                            ->filter()
                            ->unique()
                            ->values()
                            ->toArray();

                        $availableSerials = array_values(array_diff($availableSerials, $usedSerials));

                        // Validate serials
                        if (!empty($availableSerials)) {
                            if ($newQuantity > 0 && $newQuantity !== count($selectedSerials)) {
                                throw new \Exception('Select exactly ' . $newQuantity . ' serial number(s) for ' . $itemModel->item_name . '.');
                            }
                            $invalidSerials = array_diff($selectedSerials, $availableSerials);
                            if (!empty($invalidSerials)) {
                                throw new \Exception('Invalid serial numbers selected for ' . $itemModel->item_name . '.');
                            }
                        } elseif (!empty($selectedSerials)) {
                            throw new \Exception('Serial numbers not available for ' . $itemModel->item_name . '.');
                        }

                        // Track inventory change
                        if ($quantityDifference != 0) {
                            if (!isset($inventoryChanges[$itemModel->item_name])) {
                                $inventoryChanges[$itemModel->item_name] = ['quantity' => 0, 'serials' => []];
                            }
                            $inventoryChanges[$itemModel->item_name]['quantity'] += $quantityDifference;
                        }

                        // Update quantity
                        $itemModel->update(['quantity' => $newQuantity]);

                        // Sync serials
                        $currentSerials = $itemModel->serial_numbers->pluck('serial_number')->all();
                        $toAdd = array_diff($selectedSerials, $currentSerials);
                        $toRemove = array_diff($currentSerials, $selectedSerials);

                        if (!empty($toRemove)) {
                            RecoveryStoreRequisitionItemSerialNumber::where('item_id', $itemKey)
                                ->whereIn('serial_number', $toRemove)
                                ->delete();

                            // Track removed serials for inventory
                            if (!isset($inventoryChanges[$itemModel->item_name])) {
                                $inventoryChanges[$itemModel->item_name] = ['quantity' => 0, 'serials' => []];
                            }
                            $inventoryChanges[$itemModel->item_name]['serials'] = array_merge(
                                $inventoryChanges[$itemModel->item_name]['serials'],
                                array_map(fn($s) => ['action' => 'remove', 'serial' => $s], $toRemove)
                            );
                        }

                        foreach ($toAdd as $serial) {
                            RecoveryStoreRequisitionItemSerialNumber::create([
                                'item_id' => $itemKey,
                                'serial_number' => $serial,
                                'returned' => false,
                            ]);

                            // Track added serials for inventory
                            if (!isset($inventoryChanges[$itemModel->item_name])) {
                                $inventoryChanges[$itemModel->item_name] = ['quantity' => 0, 'serials' => []];
                            }
                            $inventoryChanges[$itemModel->item_name]['serials'][] = ['action' => 'add', 'serial' => $serial];
                        }

                        // Delete if quantity = 0
                        if ($newQuantity === 0) {
                            $itemModel->delete();
                        }
                    }
                }

                // Update RecoveryStore inventory based on tracked changes
                foreach ($inventoryChanges as $itemName => $changes) {
                    $recoveryStore = RecoveryStore::firstOrNew(['item_name' => $itemName]);
                    $recoveryStore->quantity = ((int) $recoveryStore->quantity) + $changes['quantity'];
                    $recoveryStore->save();

                    // Handle serial number changes
                    foreach ($changes['serials'] as $serialChange) {
                        if (is_array($serialChange) && isset($serialChange['action'])) {
                            if ($serialChange['action'] === 'add') {
                                RecoveryStoreSerialNumber::create([
                                    'recovery_store_id' => $recoveryStore->id,
                                    'serial_numbers' => $serialChange['serial'],
                                ]);
                            } elseif ($serialChange['action'] === 'remove') {
                                RecoveryStoreSerialNumber::where('recovery_store_id', $recoveryStore->id)
                                    ->where('serial_numbers', $serialChange['serial'])
                                    ->delete();
                            }
                        } else {
                            // For new items, just add the serial
                            RecoveryStoreSerialNumber::create([
                                'recovery_store_id' => $recoveryStore->id,
                                'serial_numbers' => $serialChange,
                            ]);
                        }
                    }

                    // Clean up if quantity becomes zero
                    if ($recoveryStore->quantity <= 0) {
                        $recoveryStore->delete();
                    }
                }
            });

            return redirect()->route('recovery.index')
                ->with('success', 'Recovery store requisition updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Could not update: ' . $e->getMessage())
                ->withInput();
        }
    }


    private function getAvailableSerialsByItem(string $storeRequisitionId): array
    {
        $serialsByItem = StoreRequisitionSerialNumber::where('store_requisition_id', $storeRequisitionId)
            ->with('itemSerialNumber')
            ->get()
            ->groupBy(fn($history) => optional($history->itemSerialNumber)->item_name)
            ->map(function ($histories) {
                return $histories->map(function ($history) {
                    return optional($history->itemSerialNumber)->serial_number;
                })->filter(fn($serial) => $serial !== null && $serial !== '')
                    ->unique()
                    ->values()
                    ->all();
            });

        $usedSerialsByItem = RecoveryStoreRequisitionItemSerialNumber::whereHas('item.recovery_store_requisition', function ($query) use ($storeRequisitionId) {
            $query->where('store_requisition_id', $storeRequisitionId);
        })
            ->with('item')
            ->get()
            ->groupBy(fn($serial) => optional($serial->item)->item_name)
            ->map(function ($serials) {
                return $serials->flatMap(function ($serial) {
                    return $this->extractSerialValues($serial->serial_number);
                })->filter(fn($value) => $value !== '')
                    ->unique()
                    ->values()
                    ->all();
            });

        return $serialsByItem->map(function ($serials, $itemName) use ($usedSerialsByItem) {
            $used = $usedSerialsByItem[$itemName] ?? [];
            return array_values(array_diff($serials, $used));
        })->toArray();
    }
}



