<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreReturn;
use App\Models\StoreReturnItem;
use App\Models\StoreReturnItemSerialNumber;
use App\Models\RecoveryStore;
use App\Models\RecoveryStoreRequisition;
use App\Models\RecoveryStoreRequisitionItem;
use App\Models\RecoveryStoreRequisitionItemSerialNumber;
use App\Models\RecoveryStoreSerialNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class StoreReturnController extends Controller
{
    /**
     * Display all store returns.
     */
    public function index()
    {
        $stores = StoreReturn::latest()->with(['creator', 'recovery_store_requisition', 'recovery_store_requisition.destinationLink.destination'])->get();
        return view('returns.index', compact('stores'));
    }

    /**
     * Show form to create a new store return.
     */
    public function create()
    {
        $requisitions = RecoveryStoreRequisition::with([
            'destinationLink.destination',  // for client name
            'items',                       // recovery items
        ])
            ->orderBy('store_requisition_id')
            ->get()
            ->filter(function ($requisition) {
                foreach ($requisition->items as $item) {
                    $returned = (int) ($item->returned_quantity ?? 0);
                    if ($returned < (int) $item->quantity) {
                        return true;
                    }
                }
                return false;
            });

        return view('returns.create', compact('requisitions'));
    }

    /**
     * Store a new return.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'requisition_id' => 'required|exists:recovery_store_requisitions,store_requisition_id',
            'approved_by' => 'required|string',
            'quantities' => 'required|array',
            'quantities.*' => 'nullable|integer|min:1',
            'return_date' => 'required|date|before_or_equal:today',
            'serials' => 'nullable|array',
            'serials.*.*' => 'string',
        ]);

        $quantities = $this->filterQuantities($validated['quantities']);

        if (empty($quantities)) {
            return redirect()->back()->with('error', 'Please enter at least one return quantity')->withInput();
        }

        try {
            DB::transaction(function () use ($validated, $quantities) {
                $requisition = RecoveryStoreRequisition::where('store_requisition_id', $validated['requisition_id'])->firstOrFail();

                $storeReturn = StoreReturn::create([
                    'recovery_requisition_id' => $requisition->recovery_requisition_id,
                    'created_by' => Auth::id(),
                    'approved_by' => $validated['approved_by'],
                    'returned_on' => $validated['return_date'],
                ]);

                $serialSelections = $validated['serials'] ?? [];

                foreach ($quantities as $itemName => $returnQty) {
                    $selectedSerials = $serialSelections[$itemName] ?? [];
                    $this->processReturnItem($storeReturn, $itemName, $returnQty, $selectedSerials);
                }
            });

            return redirect()->route('returns.index')->with('success', 'Store return created successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not create due to internal server error. please consult technical support.')->withInput();
        }
    }
    /**
     * Search store returns by requisition ID or old client.
     */
    public function search()
    {
        $query = request('q');

        $stores = StoreReturn::with('recovery_store_requisition')
            ->whereHas('recovery_store_requisition', function ($q) use ($query) {
                $q->where('store_requisition_id', 'LIKE', "%$query%")
                    ->orWhere('old_client', 'LIKE', "%$query%");
            })
            ->get();

        return view('returns.search', compact('stores', 'query'));
    }

    /**
     * Delete a store return and restore balances.
     */
    public function destroy($requisition_id)
    {
        try {
            //  Load the StoreReturn with items and serial links
            //  Relationship on StoreReturnItem is `serial_numbers`, not `serialNumbers`
            $storeReturn = StoreReturn::with(['items.serial_numbers'])
                ->where('recovery_requisition_id', $requisition_id)
                ->firstOrFail();

            DB::transaction(function () use ($storeReturn) {
                //  Restore each return item
                foreach ($storeReturn->items as $item) {
                    $this->restoreStoreReturnItem($storeReturn, $item);
                }

                //  Delete the StoreReturn record itself
                $storeReturn->delete();
            });

            return redirect()->back()->with('success', 'Store Return deleted and balances restored successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not delete due to internal server error.');
        }
    }
    /**
     * Load materials for a given requisition.
     */
    public function loadMaterials($storeRequisitionId)
    {
        try {
            $requisition = RecoveryStoreRequisition::where('store_requisition_id', $storeRequisitionId)
                // Relationship on RecoveryStoreRequisitionItem is `serial_numbers`, not `serialNumbers`
                ->with(['items.serial_numbers'])
                ->firstOrFail();

            // Prepare materials with balances and serials
            $materials = $this->prepareMaterialsForReturn($requisition);

            return response()->json([
                'success' => true,
                'requisition' => [
                    'store_requisition_id' => $requisition->store_requisition_id,
                ],
                'materials' => $materials,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading materials'
            ]);
        }
    }


    /**
     * Show edit form for a store return.
     */
    public function editForm($requisitionId)
    {
        $storeReturn = StoreReturn::with([
            'items.serial_numbers.itemSerialNumber',
            'recovery_store_requisition.items',
            'recovery_store_requisition.items.destinationLink.destination',
        ])
            ->whereHas('recovery_store_requisition', fn($q) => $q->where('store_requisition_id', $requisitionId))
            ->firstOrFail();

        $materials = $this->prepareMaterialsForReturn($storeReturn->recovery_store_requisition);
        $availableSerials = $materials->mapWithKeys(function ($material) {
            $serials = collect($material['serials'] ?? [])
                ->pluck('serial_number')
                ->filter()
                ->values()
                ->toArray();

            return [$material['item_name'] => $serials];
        });

        return view('returns.edit', compact('storeReturn', 'requisitionId', 'availableSerials'));
    }

    /**
     * Update all return quantities for a store return.
     */
    public function updateAll(Request $request, $requisitionId)
    {
        $validated = $request->validate([
            'approved_by' => 'required|string',
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:1',
            'return_date' => 'required|date|before_or_equal:today',
            'serials' => 'nullable|array',
            'serials.*' => 'array',
            'serials.*.*' => 'string',
        ]);

        $submittedQuantities = array_map('intval', $validated['quantities']);
        $serialSelections = $validated['serials'] ?? [];

        $storeReturn = StoreReturn::with(['items.serial_numbers.itemSerialNumber', 'recovery_store_requisition'])
            ->whereHas('recovery_store_requisition', fn($q) => $q->where('store_requisition_id', $requisitionId))
            ->firstOrFail();

        $existingItems = $storeReturn->items->keyBy('item_name');
        $hasChanges = false;

        // Check for changes in quantity or serials
        foreach ($submittedQuantities as $itemName => $newQuantity) {
            $currentItem = $existingItems->get($itemName);
            $currentQuantity = optional($currentItem)->quantity;

            // Quantity change
            if (is_null($currentQuantity) || (int) $currentQuantity !== $newQuantity) {
                $hasChanges = true;
                break;
            }

            // Serial change
            $currentSerials = optional($currentItem)->serial_numbers->pluck('itemSerialNumber.serial_number')->toArray();
            $newSerials = $serialSelections[$itemName] ?? [];
            sort($currentSerials);
            sort($newSerials);

            if ($currentSerials !== $newSerials) {
                $hasChanges = true;
                break;
            }
        }

        if (!$hasChanges) {
            return redirect()->back()
                ->with('error', 'Nothing was changed. Please adjust at least one return quantity or serial selection.')
                ->withInput();
        }

        $quantities = $this->filterQuantities($submittedQuantities);

        try {
            DB::transaction(function () use ($validated, $storeReturn, $quantities, $serialSelections) {
                // Update main return details
                $storeReturn->update([
                    'approved_by' => $validated['approved_by'],
                    'returned_on' => $validated['return_date'],
                ]);

                // Update each return item
                foreach ($quantities as $itemName => $newQty) {
                    $serials = array_map('trim', $serialSelections[$itemName] ?? []);
                    $this->updateReturnItem($storeReturn, $itemName, $newQty, $serials);
                }
            });

            return redirect()->route('returns.index')->with('success', 'Store return updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not update due to internal server error. Please consult technical support.')->withInput();
        }
    }

    /* ------------------ PRIVATE HELPERS ------------------ */

    private function filterQuantities(array $quantities): array
    {
        return array_filter($quantities, fn($q) => !is_null($q) && $q > 0);
    }

    /**
     * Process a return item.
     */
    private function processReturnItem(StoreReturn $storeReturn, string $itemName, int $returnQuantity, array $selectedSerials = [])
    {
        // Get RecoveryStore (current holding)
        $recoveryStoreItem = RecoveryStore::firstWhere('item_name', $itemName);
        if (!$recoveryStoreItem) {
            throw new \Exception("Item $itemName not found in recovery store");
        }

        // Get original requisition item
        $requisitionItem = RecoveryStoreRequisitionItem::where('recovery_requisition_id', $storeReturn->recovery_requisition_id)
            ->where('item_name', $itemName)
            ->first();

        //  Get main store item
        $storeInventoryItem = Store::firstWhere('item_name', $itemName);

        //  Validate quantity against balance
        $availableBalance = $recoveryStoreItem->quantity;
        if (!empty($selectedSerials)) {
            $availableBalance = count($selectedSerials);
            if ($availableBalance !== $returnQuantity) {
                throw new \Exception("Select exactly $returnQuantity serial(s) for $itemName.");
            }
        } elseif ($returnQuantity > $availableBalance) {
            throw new \Exception("Insufficient recovery store quantity for $itemName");
        }

        $requisitionBalance = 0;
        if ($requisitionItem) {
            $requisitionItem->returned_quantity = ($requisitionItem->returned_quantity ?? 0) + $returnQuantity;
            $requisitionItem->save();
            $requisitionBalance = max(($requisitionItem->quantity ?? 0) - ($requisitionItem->returned_quantity ?? 0), 0);
        }

        $destinationLinkId = $requisitionItem->destination_link_id ?? null;

        //  Create StoreReturnItem (history)
        $returnItem = StoreReturnItem::create([
            'store_return_id' => $storeReturn->id,
            'destination_link_id' => $destinationLinkId,
            'item_name' => $itemName,
            'quantity' => $returnQuantity,
            'balance' => $requisitionBalance,
        ]);

        //  Handle serial numbers
        foreach ($selectedSerials as $serial) {
            if ($requisitionItem) {
                $requisitionSerial = RecoveryStoreRequisitionItemSerialNumber::where('item_id', $requisitionItem->id)
                    ->where('serial_number', $serial)
                    ->first();

                if ($requisitionSerial) {
                    StoreReturnItemSerialNumber::create([
                        'store_return_item_id' => $returnItem->id,
                        'item_serial_number_id' => $requisitionSerial->id,
                    ]);

                    // Remove serial from RecoveryStoreSerialNumbers
                    $recoverySerial = RecoveryStoreSerialNumber::where('serial_numbers', $serial)->first();
                    if ($recoverySerial)
                        $recoverySerial->delete();
                }
            }
        }

        //  Reduce RecoveryStore (current holding) without deleting record
        $recoveryStoreItem->quantity -= $returnQuantity;
        $recoveryStoreItem->save();

        //  Update main Store inventory
        if ($storeInventoryItem) {
            $storeInventoryItem->quantity += $returnQuantity;
            $storeInventoryItem->save();
        } else {
            Store::create([
                'item_name' => $itemName,
                'quantity' => $returnQuantity,
            ]);
        }
    }

    /**
     * Restore a return item (used in deletion).
     */
    private function restoreStoreReturnItem(StoreReturn $storeReturn, StoreReturnItem $item)
    {
        $itemName = $item->item_name;
        $quantityToRestore = $item->quantity;

        // Restore RecoveryStore (current holding)
        $recoveryStoreItem = RecoveryStore::firstWhere('item_name', $itemName);
        if ($recoveryStoreItem) {
            $recoveryStoreItem->quantity += $quantityToRestore;
            $recoveryStoreItem->save();
        } else {
            $recoveryStoreItem = RecoveryStore::create([
                'item_name' => $itemName,
                'quantity' => $quantityToRestore
            ]);
        }

        //  Reduce main Store inventory (deduct returned quantity)
        $storeItem = Store::firstWhere('item_name', $itemName);
        if ($storeItem) {
            $storeItem->quantity = max($storeItem->quantity - $quantityToRestore, 0);
            $storeItem->save();
        }

        // Update RecoveryStoreRequisitionItem returned_quantity (history)
        $requisitionItem = RecoveryStoreRequisitionItem::where('recovery_requisition_id', $storeReturn->recovery_requisition_id)
            ->where('item_name', $itemName)
            ->first();

        if ($requisitionItem) {
            $requisitionItem->returned_quantity = max(($requisitionItem->returned_quantity ?? 0) - $quantityToRestore, 0);
            $requisitionItem->save();
        }

        //  Restore serial numbers (if any)
        //  Relationship on StoreReturnItem is `serial_numbers`, not `serialNumbers`
        $serialLinks = $item->serial_numbers ?? []; // StoreReturnItemSerialNumber collection
        foreach ($serialLinks as $link) {
            $requisitionSerial = RecoveryStoreRequisitionItemSerialNumber::find($link->item_serial_number_id);
            if ($requisitionSerial) {
                // Restore to RecoveryStoreSerialNumber
                if (!$recoveryStoreItem) {
                    $recoveryStoreItem = RecoveryStore::firstOrCreate(
                        ['item_name' => $itemName],
                        ['quantity' => 0]
                    );
                }

                RecoveryStoreSerialNumber::firstOrCreate(
                    ['serial_numbers' => $requisitionSerial->serial_number],
                    ['recovery_store_id' => $recoveryStoreItem->id]
                );

                // Optionally, you may unlink StoreReturnItemSerialNumber record
                $link->delete();
            }
        }

        //  Finally, delete the StoreReturnItem record
        $item->delete();
    }


    /**
     * Update an existing return item quantity.
     */
    /**
     * Update an existing return item quantity and serial numbers.
     */
    private function updateReturnItem(StoreReturn $storeReturn, string $itemName, int $newQuantity, array $submittedSerials = [])
    {
        $item = $storeReturn->items->firstWhere('item_name', $itemName);
        if (!$item) {
            throw new \Exception("Return item not found for {$itemName}");
        }

        $item->loadMissing('serial_numbers.itemSerialNumber');

        $oldQuantity = $item->quantity;
        $diff = $newQuantity - $oldQuantity;

        // Normalize submitted serials
        $submittedSerials = array_values(array_unique(array_filter($submittedSerials)));

        // Load related entities
        $recoveryStoreItem = RecoveryStore::firstWhere('item_name', $itemName);
        $storeItem = Store::firstWhere('item_name', $itemName);
        $requisitionItem = RecoveryStoreRequisitionItem::where('recovery_requisition_id', $storeReturn->recovery_requisition_id)
            ->where('item_name', $itemName)
            ->first();

        // ----------------------------
        // Handle quantity increase
        // ----------------------------
        if ($diff > 0) {
            if (!$recoveryStoreItem || $recoveryStoreItem->quantity < $diff) {
                throw new \Exception("Insufficient recovery store quantity for {$itemName}");
            }

            // Serial validation
            if (!empty($submittedSerials) && count($submittedSerials) !== $diff) {
                throw new \Exception("Select exactly {$diff} serial(s) for {$itemName}");
            }

            // Deduct from recovery store
            $recoveryStoreItem->quantity -= $diff;
            $recoveryStoreItem->save();

            // Increase store inventory
            if ($storeItem) {
                $storeItem->quantity += $diff;
                $storeItem->save();
            } else {
                Store::create(['item_name' => $itemName, 'quantity' => $diff]);
            }

            // Update requisition returned quantity
            if ($requisitionItem) {
                $requisitionItem->returned_quantity = ($requisitionItem->returned_quantity ?? 0) + $diff;
                $requisitionItem->save();
            }
        }

        // ----------------------------
        // Handle quantity decrease
        // ----------------------------
        elseif ($diff < 0) {
            $restoreQty = abs($diff);

            // Restore to recovery store
            if ($recoveryStoreItem) {
                $recoveryStoreItem->quantity += $restoreQty;
                $recoveryStoreItem->save();
            } else {
                RecoveryStore::create(['item_name' => $itemName, 'quantity' => $restoreQty]);
            }

            // Reduce store inventory
            if (!$storeItem || $storeItem->quantity < $restoreQty) {
                throw new \Exception("Insufficient store quantity to reduce for {$itemName}");
            }
            $storeItem->quantity -= $restoreQty;
            $storeItem->save();

            // Update requisition returned quantity
            if ($requisitionItem) {
                $requisitionItem->returned_quantity = max(($requisitionItem->returned_quantity ?? 0) - $restoreQty, 0);
                $requisitionItem->save();
            }
        }

        // ----------------------------
        // Handle serial numbers
        // ----------------------------
        if ($requisitionItem) {
            $currentSerialLinks = $item->serial_numbers->keyBy('item_serial_number_id');

            // Determine serials to remove
            $submittedSerialIds = RecoveryStoreRequisitionItemSerialNumber::where('item_id', $requisitionItem->id)
                ->whereIn('serial_number', $submittedSerials)
                ->pluck('id')
                ->toArray();

            $serialsToRemove = $currentSerialLinks->keys()->diff($submittedSerialIds);

            // Remove old serials and restore to recovery store
            foreach ($serialsToRemove as $serialId) {
                $link = $currentSerialLinks[$serialId];
                $serialNumber = RecoveryStoreRequisitionItemSerialNumber::find($serialId);
                if ($serialNumber) {
                    RecoveryStoreSerialNumber::firstOrCreate([
                        'serial_numbers' => $serialNumber->serial_number,
                        'recovery_store_id' => $recoveryStoreItem->id,
                    ]);
                }
                $link->delete();
            }

            // Add new serials
            foreach ($submittedSerials as $serial) {
                $serialRecord = RecoveryStoreRequisitionItemSerialNumber::where('item_id', $requisitionItem->id)
                    ->where('serial_number', $serial)
                    ->first();

                if (!$serialRecord) {
                    throw new \Exception("Serial {$serial} is not valid for {$itemName}");
                }

                if (!StoreReturnItemSerialNumber::where('item_serial_number_id', $serialRecord->id)->exists()) {
                    StoreReturnItemSerialNumber::create([
                        'store_return_item_id' => $item->id,
                        'item_serial_number_id' => $serialRecord->id,
                    ]);
                }

                // Remove from recovery store if exists
                $recoverySerial = RecoveryStoreSerialNumber::where('serial_numbers', $serial)->first();
                if ($recoverySerial)
                    $recoverySerial->delete();
            }
        }

        // ----------------------------
        // Update return item record
        // ----------------------------
        $item->quantity = $newQuantity;
        $item->balance = $requisitionItem
            ? max(($requisitionItem->quantity ?? 0) - ($requisitionItem->returned_quantity ?? 0), 0)
            : 0;
        $item->save();
    }

    /**
     * Prepare materials collection for return.
     */
    /**
     * Prepare materials for return (serial and non-serial items combined)
     */
    private function prepareMaterialsForReturn(RecoveryStoreRequisition $requisition)
    {
        $recoveryStoreItems = RecoveryStore::where('quantity', '>', 0)->get()->keyBy('item_name');

        $recoveredByItem = $requisition->items()->pluck('quantity', 'item_name');
        $storeReturnIds = StoreReturn::where('recovery_requisition_id', $requisition->recovery_requisition_id)->pluck('id');
        $returnedByItem = StoreReturnItem::whereIn('store_return_id', $storeReturnIds)
            ->select('item_name', DB::raw('SUM(quantity) as returned'))
            ->groupBy('item_name')
            ->pluck('returned', 'item_name');

        $groupedMaterials = $requisition->items()->with('serial_numbers')->get()
            ->groupBy('item_name')
            ->map(function ($items) use ($recoveredByItem, $returnedByItem, $recoveryStoreItems) {
                $name = $items->first()->item_name;
                $recovered = (int) ($recoveredByItem[$name] ?? 0);
                $returned = (int) ($returnedByItem[$name] ?? 0);
                $availableInRecoveryStore = (int) ($recoveryStoreItems[$name]->quantity ?? 0);
                $balance = max($recovered - $returned, 0);
                $balance = min($balance, $availableInRecoveryStore);

                // Serial numbers as objects with snake_case
                $serials = $items->flatMap(function ($item) {
                    return $item->serial_numbers->map(fn($sn) => [
                        'serial_number' => $sn->serial_number,
                        'returned' => (int) $sn->returned,
                    ]);
                })->filter(fn($s) => $s['returned'] === 0)->values()->toArray();

                return [
                    'id' => $items->first()->id,
                    'item_name' => $name,
                    'recovered' => $recovered,
                    'returned' => $returned,
                    'balance' => $balance,
                    'item_ids' => $items->pluck('id')->toArray(),
                    'serials' => $serials,
                ];
            });

        // Only return materials tied to the selected requisition
        return $groupedMaterials->values();
    }
}