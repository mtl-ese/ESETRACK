<?php

namespace App\Http\Controllers;

use App\Models\EmergencyRequisition;
use App\Models\EmergencyRequisitionItem;
use App\Models\EmergencyRequisitionItemSerial;
use App\Models\ReturnsStore;
use App\Models\ReturnsStoreSerialNumber;
use App\Models\Store;
use App\Models\EmergencyReturn;
use App\Models\EmergencyReturnItem;
use App\Models\EmergencyReturnItemSerialNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class EmergencyReturnController extends Controller
{
    /**
     * Display all emergency returns.
     */
    public function index()
    {
        $returns = EmergencyReturn::latest()->with(['requisition', 'creator'])->get();
        return view('emergency.return.index', compact('returns'));
    }

    /**
     * Show form to create a new emergency return.
     */
    public function create()
    {
        $requisitions = EmergencyRequisition::with('items')
            ->orderBy('requisition_id')
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

        return view('emergency.return.create', compact('requisitions'));
    }

    /**
     * Store a new emergency return.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'requisition_id' => 'required|exists:emergency_requisitions,requisition_id',
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
                $requisition = EmergencyRequisition::where('requisition_id', $validated['requisition_id'])->firstOrFail();

                $emergencyReturn = EmergencyReturn::create([
                    'emergency_requisition_id' => $requisition->requisition_id,
                    'created_by' => Auth::id(),
                    'approved_by' => $validated['approved_by'],
                    'returned_on' => $validated['return_date'],
                ]);

                $serialSelections = $validated['serials'] ?? [];

                foreach ($quantities as $itemName => $returnQty) {
                    $selectedSerials = $serialSelections[$itemName] ?? [];
                    $this->processReturnItem($emergencyReturn, $itemName, $returnQty, $selectedSerials);
                }
            });

            return redirect()->route('emergency.return.index')->with('success', 'Emergency return created successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not create due to internal server error. please consult technical support.')->withInput();
        }
    }

    /**
     * Search emergency returns by requisition ID.
     */
    public function search()
    {
        $query = request('q');

        $returns = EmergencyReturn::with('requisition')
            ->whereHas('requisition', function ($q) use ($query) {
                $q->where('requisition_id', 'LIKE', "%$query%");
            })
            ->get();

        return view('emergency.return.search', compact('returns', 'query'));
    }

    /**
     * Delete an emergency return and restore balances.
     */
    public function destroy($requisition_id)
    {
        try {
            $emergencyReturn = EmergencyReturn::with(['items.serial_numbers'])
                ->where('emergency_requisition_id', $requisition_id)
                ->firstOrFail();

            DB::transaction(function () use ($emergencyReturn) {
                foreach ($emergencyReturn->items as $item) {
                    $this->restoreReturnItem($emergencyReturn, $item);
                }

                $emergencyReturn->delete();
            });

            return redirect()->back()->with('success', 'Emergency Return deleted and balances restored successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not delete due to internal server error.');
        }
    }

    /**
     * Load materials for a given requisition.
     */
    public function loadMaterials($requisitionId)
    {
        try {
            $requisition = EmergencyRequisition::where('requisition_id', $requisitionId)
                ->with(['items.serial_numbers'])
                ->firstOrFail();

            $materials = $this->prepareMaterialsForReturn($requisition);

            return response()->json([
                'success' => true,
                'requisition' => [
                    'requisition_id' => $requisition->requisition_id,
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
     * Show edit form for an emergency return.
     */
    public function editForm($requisitionId)
    {
        $emergencyReturn = EmergencyReturn::with([
            'items.serial_numbers.itemSerialNumber',
            'requisition.items',
        ])
            ->where('emergency_requisition_id', $requisitionId)
            ->firstOrFail();

        $materials = $this->prepareMaterialsForReturn($emergencyReturn->requisition);
        $availableSerials = $materials->mapWithKeys(function ($material) {
            $serials = collect($material['serials'] ?? [])
                ->pluck('serial_number')
                ->filter()
                ->values()
                ->toArray();

            return [$material['item_name'] => $serials];
        });

        return view('emergency.return.edit', compact('emergencyReturn', 'requisitionId', 'availableSerials'));
    }

    /**
     * Update all return quantities for an emergency return.
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

        $emergencyReturn = EmergencyReturn::with(['items.serial_numbers.itemSerialNumber', 'requisition'])
            ->where('emergency_requisition_id', $requisitionId)
            ->firstOrFail();

        $existingItems = $emergencyReturn->items->keyBy('item_name');
        $hasChanges = false;

        // Detect header changes: approved_by or return date
        $existingApprovedBy = $emergencyReturn->approved_by;
        $existingReturnedOn = $emergencyReturn->returned_on ? date('Y-m-d', strtotime($emergencyReturn->returned_on)) : null;
        $submittedReturnedOn = date('Y-m-d', strtotime($validated['return_date']));

        if ($existingApprovedBy !== $validated['approved_by'] || $existingReturnedOn !== $submittedReturnedOn) {
            $hasChanges = true;
        }

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
                ->with('error', 'Nothing was changed. Please adjust at least one return quantity, serial selection, or the return header details.')
                ->withInput();
        }

        $quantities = $this->filterQuantities($submittedQuantities);

        try {
            DB::transaction(function () use ($validated, $emergencyReturn, $quantities, $serialSelections) {
                // Update main return details
                $emergencyReturn->update([
                    'approved_by' => $validated['approved_by'],
                    'returned_on' => $validated['return_date'],
                ]);

                // Update each return item
                foreach ($quantities as $itemName => $newQty) {
                    $serials = array_map('trim', $serialSelections[$itemName] ?? []);
                    $this->updateReturnItem($emergencyReturn, $itemName, $newQty, $serials);
                }
            });

            return redirect()->route('emergency.return.index')->with('success', 'Emergency return updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not update due to internal server error. Please consult technical support.')->withInput();
        }
    }

    /* ==================== PRIVATE HELPERS ==================== */

    private function filterQuantities(array $quantities): array
    {
        return array_filter($quantities, fn($q) => !is_null($q) && $q > 0);
    }

    /**
     * Process a return item.
     */
    private function processReturnItem(EmergencyReturn $emergencyReturn, string $itemName, int $returnQuantity, array $selectedSerials = [])
    {
        $requisitionItem = EmergencyRequisitionItem::where('emergency_requisition_id', $emergencyReturn->emergency_requisition_id)
            ->where('item_name', $itemName)
            ->first();

        if (!$requisitionItem) {
            throw new \Exception("Item $itemName not found in requisition");
        }

        // Validate quantity against balance
        $availableBalance = $requisitionItem->quantity - ($requisitionItem->returned_quantity ?? 0);

        if (!empty($selectedSerials)) {
            $availableBalance = count($selectedSerials);
            if ($availableBalance !== $returnQuantity) {
                throw new \Exception("Select exactly $returnQuantity serial(s) for $itemName.");
            }
        } elseif ($returnQuantity > $availableBalance) {
            throw new \Exception("Insufficient balance for $itemName. Available: $availableBalance");
        }

        $requisitionBalance = max($requisitionItem->quantity - (($requisitionItem->returned_quantity ?? 0) + $returnQuantity), 0);

        // Create EmergencyReturnItem (history)
        $returnItem = EmergencyReturnItem::create([
            'emergency_return_id' => $emergencyReturn->id,
            'item_name' => $itemName,
            'quantity' => $returnQuantity,
            'balance' => $requisitionBalance,
        ]);

        // Handle serial numbers
        foreach ($selectedSerials as $serial) {
            $requisitionSerial = EmergencyRequisitionItemSerial::where('item_id', $requisitionItem->id)
                ->where('serial_number', $serial)
                ->first();

            if ($requisitionSerial) {
                EmergencyReturnItemSerialNumber::create([
                    'emergency_return_item_id' => $returnItem->id,
                    'item_serial_number_id' => $requisitionSerial->id,
                ]);

                // Mark the requisition serial as returned
                $requisitionSerial->returned = true;
                $requisitionSerial->save();
            }
        }

        // Update requisition item returned_quantity and balance
        $requisitionItem->returned_quantity = ($requisitionItem->returned_quantity ?? 0) + $returnQuantity;
        $requisitionItem->balance = max($requisitionItem->quantity - ($requisitionItem->returned_quantity ?? 0), 0);
        $requisitionItem->save();

        // Update main stores based on the source
        if ($requisitionItem->from === 'stores') {
            $storeItem = Store::firstWhere('item_name', $itemName);
            if ($storeItem) {
                $storeItem->quantity += $returnQuantity;
                $storeItem->save();
            } else {
                Store::create(['item_name' => $itemName, 'quantity' => $returnQuantity]);
            }
        } else {
            $returnStoreItem = ReturnsStore::firstWhere('item_name', $itemName);
            if ($returnStoreItem) {
                $returnStoreItem->quantity += $returnQuantity;
                $returnStoreItem->save();
            } else {
                $returnStoreItem = ReturnsStore::create(['item_name' => $itemName, 'quantity' => $returnQuantity]);
            }

            // Add serials to returns store if applicable
            foreach ($selectedSerials as $serial) {
                ReturnsStoreSerialNumber::create([
                    'returns_store_id' => $returnStoreItem->id,
                    'serial_numbers' => $serial,
                ]);
            }
        }
    }

    /**
     * Restore a return item (used in deletion).
     */
    private function restoreReturnItem(EmergencyReturn $emergencyReturn, EmergencyReturnItem $item)
    {
        $itemName = $item->item_name;
        $quantityToRestore = $item->quantity;

        $requisitionItem = EmergencyRequisitionItem::where('emergency_requisition_id', $emergencyReturn->emergency_requisition_id)
            ->where('item_name', $itemName)
            ->first();

        if ($requisitionItem) {
            $requisitionItem->returned_quantity = max(($requisitionItem->returned_quantity ?? 0) - $quantityToRestore, 0);
            $requisitionItem->balance = max($requisitionItem->quantity - ($requisitionItem->returned_quantity ?? 0), 0);
            $requisitionItem->save();
        }

        // Deduct from destination store
        if ($requisitionItem && $requisitionItem->from === 'stores') {
            $storeItem = Store::firstWhere('item_name', $itemName);
            if ($storeItem) {
                $storeItem->quantity = max($storeItem->quantity - $quantityToRestore, 0);
                $storeItem->save();
            }
        } else {
            $returnStoreItem = ReturnsStore::firstWhere('item_name', $itemName);
            if ($returnStoreItem) {
                $returnStoreItem->quantity = max($returnStoreItem->quantity - $quantityToRestore, 0);
                $returnStoreItem->save();
            }
        }

        // Restore serial numbers (if any)
        $serialLinks = $item->serial_numbers ?? [];
        foreach ($serialLinks as $link) {
            $requisitionSerial = EmergencyRequisitionItemSerial::find($link->item_serial_number_id);
            if ($requisitionSerial) {
                // Mark the requisition serial as not returned
                $requisitionSerial->returned = false;
                $requisitionSerial->save();

                // Delete the link
                $link->delete();
            }
        }

        // Finally, delete the EmergencyReturnItem record
        $item->delete();
    }

    /**
     * Update an existing return item quantity and serial numbers.
     */
    private function updateReturnItem(EmergencyReturn $emergencyReturn, string $itemName, int $newQuantity, array $submittedSerials = [])
    {
        $item = $emergencyReturn->items->firstWhere('item_name', $itemName);
        if (!$item) {
            throw new \Exception("Return item not found for {$itemName}");
        }

        $item->loadMissing('serial_numbers.itemSerialNumber');

        $oldQuantity = $item->quantity;
        $diff = $newQuantity - $oldQuantity;

        // Normalize submitted serials
        $submittedSerials = array_values(array_unique(array_filter($submittedSerials)));

        $requisitionItem = EmergencyRequisitionItem::where('emergency_requisition_id', $emergencyReturn->emergency_requisition_id)
            ->where('item_name', $itemName)
            ->first();

        // ----------------------------
        // Handle quantity increase
        // ----------------------------
        if ($diff > 0) {
            $availableBalance = max($requisitionItem->quantity - ($requisitionItem->returned_quantity ?? 0) - $oldQuantity, 0);

            if ($availableBalance < $diff) {
                throw new \Exception("Insufficient balance for {$itemName}. Available: $availableBalance");
            }

            // Serial validation
            if (!empty($submittedSerials) && count($submittedSerials) !== $diff) {
                throw new \Exception("Select exactly {$diff} serial(s) for {$itemName}");
            }

            // Update requisition returned quantity
            if ($requisitionItem) {
                $requisitionItem->returned_quantity = ($requisitionItem->returned_quantity ?? 0) + $diff;
                $requisitionItem->balance = max($requisitionItem->quantity - ($requisitionItem->returned_quantity ?? 0), 0);
                $requisitionItem->save();
            }

            // Update stores
            if ($requisitionItem->from === 'stores') {
                $storeItem = Store::firstWhere('item_name', $itemName);
                if ($storeItem) {
                    $storeItem->quantity += $diff;
                    $storeItem->save();
                } else {
                    Store::create(['item_name' => $itemName, 'quantity' => $diff]);
                }
            } else {
                $returnStoreItem = ReturnsStore::firstWhere('item_name', $itemName);
                if ($returnStoreItem) {
                    $returnStoreItem->quantity += $diff;
                    $returnStoreItem->save();
                } else {
                    ReturnsStore::create(['item_name' => $itemName, 'quantity' => $diff]);
                }
            }
        }

        // ----------------------------
        // Handle quantity decrease
        // ----------------------------
        elseif ($diff < 0) {
            $restoreQty = abs($diff);

            // Update requisition returned quantity
            if ($requisitionItem) {
                $requisitionItem->returned_quantity = max(($requisitionItem->returned_quantity ?? 0) - $restoreQty, 0);
                $requisitionItem->balance = max($requisitionItem->quantity - ($requisitionItem->returned_quantity ?? 0), 0);
                $requisitionItem->save();
            }

            // Reduce destination stores
            if ($requisitionItem->from === 'stores') {
                $storeItem = Store::firstWhere('item_name', $itemName);
                if (!$storeItem || $storeItem->quantity < $restoreQty) {
                    throw new \Exception("Insufficient store quantity to reduce for {$itemName}");
                }
                $storeItem->quantity -= $restoreQty;
                $storeItem->save();
            } else {
                $returnStoreItem = ReturnsStore::firstWhere('item_name', $itemName);
                if (!$returnStoreItem || $returnStoreItem->quantity < $restoreQty) {
                    throw new \Exception("Insufficient return store quantity to reduce for {$itemName}");
                }
                $returnStoreItem->quantity -= $restoreQty;
                $returnStoreItem->save();
            }
        }

        // ----------------------------
        // Handle serial numbers
        // ----------------------------
        if ($requisitionItem) {
            $currentSerialLinks = $item->serial_numbers->keyBy('item_serial_number_id');

            // Determine serials to remove
            $submittedSerialIds = EmergencyRequisitionItemSerial::where('item_id', $requisitionItem->id)
                ->whereIn('serial_number', $submittedSerials)
                ->pluck('id')
                ->toArray();

            $serialsToRemove = $currentSerialLinks->keys()->diff($submittedSerialIds);

            // Remove old serials
            foreach ($serialsToRemove as $serialId) {
                $link = $currentSerialLinks[$serialId];
                $serialNumber = EmergencyRequisitionItemSerial::find($serialId);
                if ($serialNumber) {
                    // Mark the requisition serial as not returned
                    $serialNumber->returned = false;
                    $serialNumber->save();
                }
                $link->delete();
            }

            // Add new serials
            foreach ($submittedSerials as $serial) {
                $serialRecord = EmergencyRequisitionItemSerial::where('item_id', $requisitionItem->id)
                    ->where('serial_number', $serial)
                    ->first();

                if (!$serialRecord) {
                    throw new \Exception("Serial {$serial} is not valid for {$itemName}");
                }

                if (!EmergencyReturnItemSerialNumber::where('item_serial_number_id', $serialRecord->id)->exists()) {
                    EmergencyReturnItemSerialNumber::create([
                        'emergency_return_item_id' => $item->id,
                        'item_serial_number_id' => $serialRecord->id,
                    ]);

                    // Mark the requisition serial as returned
                    $serialRecord->returned = true;
                    $serialRecord->save();
                }
            }
        }

        // ----------------------------
        // Update return item record
        // ----------------------------
        $item->quantity = $newQuantity;
        $item->balance = $requisitionItem
            ? max($requisitionItem->quantity - ($requisitionItem->returned_quantity ?? 0), 0)
            : 0;
        $item->save();
    }

    /**
     * Prepare materials for return (serial and non-serial items combined)
     */
    private function prepareMaterialsForReturn(EmergencyRequisition $requisition)
    {
        $quantityByItem = $requisition->items()->pluck('quantity', 'item_name');
        $returnIds = EmergencyReturn::where('emergency_requisition_id', $requisition->requisition_id)->pluck('id');
        $returnedByItem = EmergencyReturnItem::whereIn('emergency_return_id', $returnIds)
            ->select('item_name', DB::raw('SUM(quantity) as returned'))
            ->groupBy('item_name')
            ->pluck('returned', 'item_name');

        $groupedMaterials = $requisition->items()->with('serial_numbers')->get()
            ->groupBy('item_name')
            ->map(function ($items) use ($quantityByItem, $returnedByItem) {
                $name = $items->first()->item_name;
                $quantity = (int) ($quantityByItem[$name] ?? 0);
                $returned = (int) ($returnedByItem[$name] ?? 0);
                $balance = max($quantity - $returned, 0);

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
                    'quantity' => $quantity,
                    'returned' => $returned,
                    'balance' => $balance,
                    'item_ids' => $items->pluck('id')->toArray(),
                    'serials' => $serials,
                ];
            });

        return $groupedMaterials->values();
    }
}
