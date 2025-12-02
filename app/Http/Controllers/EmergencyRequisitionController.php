<?php

namespace App\Http\Controllers;

use App\Models\EmergencyRequisition;
use App\Models\ReturnsStore;
use App\Models\ReturnsstoreSerialNumber;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmergencyRequisitionController extends Controller
{
    public function index()
    {
        $emergencies = EmergencyRequisition::latest()->with(['creator'])->get();

        return view('emergency.index', [
            'emergencies' => $emergencies
        ]);
    }

    public function create()
    {
        return view('emergency.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'requisition_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^MTL\s\d{1,10}$/', $value)) {
                        $fail('The ' . $attribute . ' must start with "MTL" followed by a space and a number.');
                    }
                }
            ],
            'initiator' => ['required', 'string'],
            'department' => ['required', 'string'],
            'approved_by' => ['required', 'string'],
            'requisition_date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        //check if id already exists
        $id = EmergencyRequisition::where('requisition_id', $validated['requisition_id'])->first();
        if ($id) {
            return redirect()
                ->back()
                ->with('error', 'Sorry, Emergency Requisition ID already exists.')
                ->withInput();
        }

        $user = Auth::user();

        $emergency = EmergencyRequisition::create([
            'requisition_id' => $validated['requisition_id'],
            'approved_by' => $validated['approved_by'],
            'initiator' => $validated['initiator'],
            'department' => $validated['department'],
            'created_by' => $user->id,
            'requested_on' => $validated['requisition_date'],
            'returned_on' => null
        ]);

        return redirect()
            ->route('emergencyItemsCreate', ['requisition_id' => $emergency->requisition_id])
            ->with('success', 'You can now add materials to the emergency requisition.');
    }

    public function search()
    {
        $emergencies = EmergencyRequisition::with(['creator'])->where("requisition_id", "LIKE", "%" . request('q') . "%")->get();

        if ($emergencies->isEmpty()) {
            return redirect()->route('emergencyIndex')->with('error', 'No records found');
        } else {
            return view("emergency.search", [
                "emergencies" => $emergencies,
                'query' => request('q')
            ]);
        }
    }

    public function destroy($requisition_id)
    {
        $emergency = EmergencyRequisition::with(['items.serial_numbers', 'return'])
            ->where('requisition_id', $requisition_id)
            ->first();


        //just for checks, nothing personal
        if (!$emergency) {
            return redirect()
                ->back()
                ->with('error', 'Emergency requisition not found');
        }

        //check if it is returned
        if ($emergency->returned_on) {
            return redirect()
                ->back()
                ->with('error', 'Sorry, Emergency requisition already returned');
        }

        //restore its items and their serial numbers
        foreach ($emergency->items as $item) {

            //do this if item is from return stores
            if ($item->from == 'return stores') {

                //restore the serial numbers
                foreach ($item->serial_numbers as $serial) {

                    //get the item id from return stores
                    $item_id = ReturnsStore::where('item_name', $item->item_name)->first()->id;

                    //return the serial numbers
                    ReturnsstoreSerialNumber::create([
                        'returns_store_id' => $item_id,
                        'serial_numbers' => $serial->serial_number
                    ]);
                }

                //update item quantity in return stores
                $returnStoreItem = ReturnsStore::where('item_name', $item->item_name)->first();
                $returnStoreItem->quantity += $item->quantity;
                $returnStoreItem->save();

                //delete the item
                $item->delete();
            } else {
                $storeItem = Store::where('item_name', $item->item_name)->first();
                $storeItem->quantity += $item->quantity;
                $storeItem->save();
            }
        }
        $emergency->delete();
        return redirect()->route('emergencyIndex')->with('success', $requisition_id . ' emergency requisition deleted successfully');
    }

    /**
     * Show the edit form for an emergency requisition
     */
    public function editForm($requisition_id)
    {
        $requisition = EmergencyRequisition::where('requisition_id', $requisition_id)->firstOrFail();

        $items = $requisition->items()->with('serial_numbers')->get();

        return view('emergency.edit', [
            'requisition' => $requisition,
            'requisition_id' => $requisition_id,
            'items' => $items
        ]);
    }

    /**
     * Update header and items for an emergency requisition
     */
    /*public function updateAll(Request $request, $requisition_id)
    {
        $validated = $request->validate([
            'initiator' => ['required', 'string'],
            'department' => ['required', 'string'],
            'approved_by' => ['required', 'string'],
            'requisition_date' => ['required', 'date', 'before_or_equal:today'],
            'items' => ['nullable', 'json'],
            'deleted_items' => ['nullable', 'json']
        ]);


        // ----------- No-change detection -----------
        $hasChanges = false;
        $requisition = EmergencyRequisition::where('requisition_id', $requisition_id)->firstOrFail();
        $existingDate = $requisition->requested_on ? date('Y-m-d', strtotime($requisition->requested_on)) : null;
        if (
            $requisition->initiator !== $validated['initiator'] ||
            $requisition->department !== $validated['department'] ||
            $requisition->approved_by !== $validated['approved_by'] ||
            $existingDate !== $validated['requisition_date']
        ) {
            $hasChanges = true;
        }
        // If deleted items provided, it's a change
        if ($request->deleted_items) {
            $hasChanges = true;
        }
        // Compare items if provided
        if (!$hasChanges && $request->items) {
            $submittedItems = json_decode($request->items, true) ?: [];
            $existingItems = EmergencyRequisition::find($requisition->id)->items()->get();
            $existingEntries = [];
            foreach ($existingItems as $item) {
                $serials = $item->serial_numbers()->pluck('serial_number')->toArray();
                $existingEntries[] = [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'quantity' => (int) $item->quantity,
                    'from' => $item->from,
                    'serials' => array_values(array_map('strval', (array) $serials)),
                ];
            }
            $submittedEntries = [];
            foreach ($submittedItems as $it) {
                $submittedEntries[] = [
                    'id' => $it['id'] ?? null,
                    'item_name' => $it['item_name'],
                    'quantity' => (int) ($it['quantity'] ?? 0),
                    'from' => $it['from'] ?? 'stores',
                    'serials' => array_values(array_map('strval', (array) ($it['serialNumbers'] ?? []))),
                ];
            }
            // Normalize by sorting serial lists and sort entries for stable comparison
            $normalize = function ($arr) {
                foreach ($arr as &$e) {
                    sort($e['serials']);
                }
                usort($arr, fn($a, $b) => ($a['id'] <=> $b['id']));
                return $arr;
            };
            $normExisting = $normalize($existingEntries);
            $normSubmitted = $normalize($submittedEntries);

            if (json_encode($normExisting) !== json_encode($normSubmitted)) {
                $hasChanges = true;
            }
        }

        if (!$hasChanges) {
            return redirect()->back()->with('error', 'Nothing was changed. Please adjust at least one field, item quantity, or serial selection.')->withInput();
        }
        try {
            \Illuminate\Support\Facades\DB::transaction(
                function () use ($validated, $request, $requisition_id) {
                    $requisition = EmergencyRequisition::where('requisition_id', $requisition_id)->firstOrFail();
                    // ...existing code...
                    $items = json_decode($request->items, true) ?: [];

                    foreach ($items as $entry) {
                        $id = $entry['id'] ?? null;
                        $name = $entry['item_name'];
                        $quantity = (int) ($entry['quantity'] ?? 0);
                        $from = $entry['from'] ?? 'stores';
                        $serials = $entry['serialNumbers'] ?? [];

                        if ($id) {
                            // update existing item
                            $existing = \App\Models\EmergencyRequisitionItem::find($id);
                            if (!$existing) {
                                throw new \Exception("Item with id {$id} not found");
                            }

                            // Only allow same item_name and from for update — changing name/from is complex
                            if ($existing->item_name !== $name || $existing->from !== $from) {
                                throw new \Exception('Changing item name or source (from) is not allowed during edit. Remove and re-add instead.');
                            }

                            $oldQty = (int) $existing->quantity;
                            // Quantity delta handling
                            if ($quantity !== $oldQty) {
                                $delta = $quantity - $oldQty;
                                if ($existing->from === 'stores') {
                                    $store = Store::firstOrCreate(['item_name' => $existing->item_name], ['quantity' => 0]);
                                    if ($delta > 0) {
                                        if ($store->quantity < $delta) {
                                            throw new \Exception("Not enough stock for {$existing->item_name}. Available: {$store->quantity}");
                                        }
                                        $store->quantity -= $delta;
                                    } else {
                                        $store->quantity += abs($delta);
                                    }
                                    $store->save();
                                } else { // return stores
                                    $returnStore = ReturnsStore::firstOrCreate(['item_name' => $existing->item_name], ['quantity' => 0]);
                                    if ($delta > 0) {
                                        if ($returnStore->quantity < $delta) {
                                            throw new \Exception("Not enough quantity in return stores for {$existing->item_name}. Available: {$returnStore->quantity}");
                                        }
                                        $returnStore->quantity -= $delta;
                                    } else {
                                        $returnStore->quantity += abs($delta);
                                    }
                                    $returnStore->save();
                                    if ($returnStore->quantity == 0)
                                        $returnStore->delete();
                                }
                                $existing->quantity = $quantity;
                                $existing->save();
                            }

                            // Handle serials reconciliation
                            $oldSerials = $existing->serial_numbers()->pluck('serial_number')->toArray();
                            $newSerials = is_array($serials) ? array_map('strval', $serials) : [];

                            $toAdd = array_values(array_diff($newSerials, $oldSerials));
                            $toRemove = array_values(array_diff($oldSerials, $newSerials));

                            // Add new serials
                            foreach ($toAdd as $s) {
                                if ($existing->from === 'return stores') {
                                    // ensure available in return store
                                    $rs = ReturnsStore::where('item_name', $existing->item_name)->first();
                                    if (!$rs)
                                        throw new \Exception("Return store item missing for {$existing->item_name}");

                                    $available = ReturnsstoreSerialNumber::where('returns_store_id', $rs->id)->pluck('serial_numbers')->toArray();
                                    if (!in_array($s, $available)) {
                                        throw new \Exception("Serial {$s} not available in return stores for {$existing->item_name}");
                                    }

                                    // remove from return store serials
                                    ReturnsstoreSerialNumber::where('returns_store_id', $rs->id)->where('serial_numbers', $s)->delete();
                                }

                                \App\Models\EmergencyRequisitionItemSerial::create([
                                    'item_id' => $existing->id,
                                    'serial_number' => $s
                                ]);
                            }

                            // Remove old serials
                            foreach ($toRemove as $s) {
                                \App\Models\EmergencyRequisitionItemSerial::where('item_id', $existing->id)->where('serial_number', $s)->delete();
                                if ($existing->from === 'return stores') {
                                    $rs = ReturnsStore::where('item_name', $existing->item_name)->first();
                                    if (!$rs) {
                                        // recreate return store entry if it doesn't exist
                                        $rs = ReturnsStore::create(['item_name' => $existing->item_name, 'quantity' => 0]);
                                    }
                                    ReturnsstoreSerialNumber::create([
                                        'returns_store_id' => $rs->id,
                                        'serial_numbers' => $s
                                    ]);
                                }
                            }

                        } else {
                            // new item: create and reduce stock as appropriate
                            if ($from === 'stores') {
                                $store = Store::where('item_name', $name)->first();
                                if (!$store)
                                    throw new \Exception("{$name} not available in stores");
                                if ($store->quantity < $quantity)
                                    throw new \Exception("Requested quantity for {$name} is more than available in stores ({$store->quantity})");
                                $store->quantity -= $quantity;
                                $store->save();
                            } else {
                                $rs = ReturnsStore::where('item_name', $name)->first();
                                if (!$rs)
                                    throw new \Exception("{$name} not available in return stores");
                                if ($rs->quantity < $quantity)
                                    throw new \Exception("Requested quantity for {$name} is more than available in return stores ({$rs->quantity})");
                                // verify serials if present
                                if (!empty($serials)) {
                                    $available = ReturnsstoreSerialNumber::where('returns_store_id', $rs->id)->pluck('serial_numbers')->toArray();
                                    if (!empty(array_diff($serials, $available))) {
                                        throw new \Exception('One or more serials are not present in return stores for ' . $name);
                                    }
                                }

                                $rs->quantity -= $quantity;
                                $rs->save();
                                if ($rs->quantity == 0)
                                    $rs->delete();
                            }

                            $created = \App\Models\EmergencyRequisitionItem::create([
                                'emergency_requisition_id' => $requisition_id,
                                'item_name' => $name,
                                'quantity' => $quantity,
                                'from' => $from,
                                'same_to_return' => 0,
                            ]);

                            // add serials if present
                            foreach ($serials as $s) {
                                \App\Models\EmergencyRequisitionItemSerial::create([
                                    'item_id' => $created->id,
                                    'serial_number' => $s
                                ]);

                                if ($from === 'return stores') {
                                    ReturnsstoreSerialNumber::where('serial_numbers', $s)->delete();
                                }
                            }
                        }
                    }
                }
            );


            return redirect()->route('emergencyIndex')->with('success', 'Emergency requisition updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }*/
    public function updateAll(Request $request, $requisition_id)
    {
        $validated = $request->validate([
            'initiator' => ['required', 'string'],
            'department' => ['required', 'string'],
            'approved_by' => ['required', 'string'],
            'requisition_date' => ['required', 'date', 'before_or_equal:today'],
            'items' => ['nullable', 'json'],
            'deleted_items' => ['nullable', 'json']
        ]);

        $requisition = EmergencyRequisition::where('requisition_id', $requisition_id)->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | 1. CHANGE DETECTION LOGIC
        |--------------------------------------------------------------------------
        */

        $hasChanges = false;

        // Compare header fields
        $existingDate = $requisition->requested_on ? date('Y-m-d', strtotime($requisition->requested_on)) : null;

        if (
            $requisition->initiator !== $validated['initiator'] ||
            $requisition->department !== $validated['department'] ||
            $requisition->approved_by !== $validated['approved_by'] ||
            $existingDate !== $validated['requisition_date']
        ) {
            $hasChanges = true;
        }

        // Deleted items only count as change if not empty
        $deletedItems = json_decode($request->deleted_items, true) ?: [];

        if (!empty($deletedItems)) {
            $hasChanges = true;
        }

        // Compare items only if no changes detected yet
        if (!$hasChanges) {
            $submittedItems = json_decode($request->items, true) ?: [];

            // Get existing items
            $existingItems = $requisition->items()->with('serial_numbers')->get();

            $existingNormalized = [];
            foreach ($existingItems as $item) {
                $serials = $item->serial_numbers()->pluck('serial_number')->toArray();
                sort($serials);

                $existingNormalized[] = [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'quantity' => (int) $item->quantity,
                    'from' => strtolower($item->from), // normalize
                    'serials' => $serials,
                ];
            }

            // Normalize submitted items
            $submittedNormalized = [];
            foreach ($submittedItems as $it) {
                $s = $it['serialNumbers'] ?? [];
                $s = array_values(array_filter($s, fn($v) => trim($v) !== ''));
                sort($s);

                $submittedNormalized[] = [
                    'id' => $it['id'] ?? null,
                    'item_name' => $it['item_name'],
                    'quantity' => (int) ($it['quantity'] ?? 0),
                    'from' => strtolower($it['from'] ?? 'stores'),
                    'serials' => $s
                ];
            }

            // Sort both arrays to make comparison stable
            $sortFn = fn($a, $b) => ($a['id'] <=> $b['id']);
            usort($existingNormalized, $sortFn);
            usort($submittedNormalized, $sortFn);

            if (json_encode($existingNormalized) !== json_encode($submittedNormalized)) {
                $hasChanges = true;
            }
        }

        if (!$hasChanges) {
            return redirect()->back()
                ->with('error', 'Nothing was changed. Please adjust at least one field or item.')
                ->withInput();
        }

        /*
        |--------------------------------------------------------------------------
        | 2. APPLY UPDATES
        |--------------------------------------------------------------------------
        */

        try {
            \DB::transaction(function () use ($validated, $request, $requisition) {

                // Update header
                $requisition->update([
                    'initiator' => $validated['initiator'],
                    'department' => $validated['department'],
                    'approved_by' => $validated['approved_by'],
                    'requested_on' => $validated['requisition_date'],
                ]);

                $items = json_decode($request->items, true) ?: [];

                foreach ($items as $entry) {
                    $id = $entry['id'] ?? null;
                    $name = $entry['item_name'];
                    $qty = (int) ($entry['quantity'] ?? 0);
                    $from = strtolower($entry['from'] ?? 'stores');
                    $serials = $entry['serialNumbers'] ?? [];

                    /*
                    |--------------------------------------------------------------------------
                    | Update existing items
                    |--------------------------------------------------------------------------
                    */
                    if ($id) {
                        $existing = \App\Models\EmergencyRequisitionItem::findOrFail($id);

                        // Prevent changing item_name or source
                        if ($existing->item_name !== $name || strtolower($existing->from) !== $from) {
                            throw new \Exception('Cannot change item name or source. Delete and re-add.');
                        }

                        $oldQty = (int) $existing->quantity;

                        /*
                        |--------------------------------------------------------------------------
                        | Quantity changes — adjust stock
                        |--------------------------------------------------------------------------
                        */
                        if ($qty !== $oldQty) {
                            $delta = $qty - $oldQty;

                            if ($from === 'stores') {
                                $store = Store::firstOrCreate(['item_name' => $name], ['quantity' => 0]);

                                if ($delta > 0 && $store->quantity < $delta) {
                                    throw new \Exception("Insufficient stock for {$name}. Available: {$store->quantity}");
                                }

                                $store->quantity -= $delta;
                                $store->save();
                            } else {
                                $returnStore = ReturnsStore::firstOrCreate(['item_name' => $name], ['quantity' => 0]);

                                if ($delta > 0 && $returnStore->quantity < $delta) {
                                    throw new \Exception("Insufficient return-store qty for {$name}. Available: {$returnStore->quantity}");
                                }

                                $returnStore->quantity -= $delta;
                                $returnStore->save();
                            }

                            $existing->quantity = $qty;
                            $existing->save();
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Serial number reconciliation
                        |--------------------------------------------------------------------------
                        */
                        $oldSerials = $existing->serial_numbers()->pluck('serial_number')->toArray();

                        $newSerials = array_values(array_filter($serials, fn($v) => trim($v) !== ''));

                        $toAdd = array_values(array_diff($newSerials, $oldSerials));
                        $toRemove = array_values(array_diff($oldSerials, $newSerials));

                        // Add serials
                        foreach ($toAdd as $sn) {
                            if ($from === 'return stores') {
                                $rs = ReturnsStore::where('item_name', $name)->first();
                                $available = ReturnsStoreSerialNumber::where('returns_store_id', $rs->id)->pluck('serial_numbers')->toArray();

                                if (!in_array($sn, $available)) {
                                    throw new \Exception("Serial {$sn} not available for {$name}");
                                }

                                // remove from return-store pool
                                ReturnsStoreSerialNumber::where('returns_store_id', $rs->id)->where('serial_numbers', $sn)->delete();
                            }

                            \App\Models\EmergencyRequisitionItemSerial::create([
                                'item_id' => $existing->id,
                                'serial_number' => $sn
                            ]);
                        }

                        // Remove serials
                        foreach ($toRemove as $sn) {
                            \App\Models\EmergencyRequisitionItemSerial::where('item_id', $existing->id)
                                ->where('serial_number', $sn)
                                ->delete();

                            if ($from === 'return stores') {
                                $rs = ReturnsStore::firstOrCreate(['item_name' => $name]);
                                ReturnsStoreSerialNumber::create([
                                    'returns_store_id' => $rs->id,
                                    'serial_numbers' => $sn
                                ]);
                            }
                        }

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NEW ITEM
                    |--------------------------------------------------------------------------
                    */
                    if ($from === 'stores') {
                        $store = Store::where('item_name', $name)->first();
                        if (!$store || $store->quantity < $qty) {
                            throw new \Exception("Insufficient store qty for {$name}");
                        }
                        $store->quantity -= $qty;
                        $store->save();
                    } else {
                        $rs = ReturnsStore::where('item_name', $name)->first();
                        if (!$rs || $rs->quantity < $qty) {
                            throw new \Exception("Insufficient return-store qty for {$name}");
                        }

                        // Validate serials
                        if (!empty($serials)) {
                            $available = ReturnsStoreSerialNumber::where('returns_store_id', $rs->id)->pluck('serial_numbers')->toArray();

                            if (!empty(array_diff($serials, $available))) {
                                throw new \Exception("Some serials are not available for {$name}");
                            }
                        }

                        $rs->quantity -= $qty;
                        $rs->save();
                    }

                    $created = \App\Models\EmergencyRequisitionItem::create([
                        'emergency_requisition_id' => $requisition->id,
                        'item_name' => $name,
                        'quantity' => $qty,
                        'from' => $from,
                        'same_to_return' => 0,
                    ]);

                    // attach serials
                    foreach ($serials as $sn) {
                        \App\Models\EmergencyRequisitionItemSerial::create([
                            'item_id' => $created->id,
                            'serial_number' => $sn
                        ]);

                        if ($from === 'return stores') {
                            ReturnsStoreSerialNumber::where('serial_numbers', $sn)->delete();
                        }
                    }
                }
            });

            return redirect()->route('emergencyIndex')->with('success', 'Emergency requisition updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

}