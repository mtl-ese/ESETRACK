<?php

namespace App\Http\Controllers;

use App\Models\RecoveryStoreRequisitionItem;
use App\Models\RecoveryStoreRequisitionItemSerialNumber;
use App\Models\RecoveryStore;
use App\Models\RecoveryStoreSerialNumber;
use App\Models\SerialNumber;
use App\Models\StoreItem;
use Illuminate\Http\Request;

class RecoveryStoreRequisitionItemController extends Controller
{

    public function index($requisition_id)
    {
        $items = RecoveryStoreRequisitionItem::with('serial_numbers', 'destinationLink.destination')
            ->where('recovery_requisition_id', $requisition_id)
            ->get();

        return view('recovery.items.index', [
            'items' => $items,
            'requisition_id' => $requisition_id,
        ]);
    }


    public function materialsIndex()
    {
        $recoverystorerequisitionItems = RecoveryStoreRequisitionItem::with(
            'recovery_store_requisition',
            'destinationLink.destination'
        )->latest()->get();
        return view('recovery.materials.index', [
            'recoverystorerequisitionItems' => $recoverystorerequisitionItems,
        ]);
    }


    /*public function create($recovery_requisition_id, $requisition_id)
    {
        return view('recovery.items.create', [
            'recovery_requisition_id' => $recovery_requisition_id,
            'requisition_id' => $requisition_id
        ]);
    }

       public function store(Request $request)
        {
            $rules = [
                'item_name' => 'required|string',
                'quantity' => 'required|integer|min:1',
                'status' => 'required|string|max:50'
            ];

            // check if item is already stored
            $item = RecoveryStoreRequisitionItem::where('item_name', $request->item_name)->where('recovery_requisition_id', $request->recovery_requisition_id)->first();

            if ($item) {
                return redirect()
                    ->back()
                    ->with('error', $request->item_name . ' is already stored in this recovery requisition.');
            }

            $store_item = StoreItem::where('item_name', $request->item_name)->where('store_requisition_id', $request->requisition_id)->first();
            if (!$store_item) {
                return redirect()
                    ->back()
                    ->with('error', $request->item_name . ' is not found in the store requisition that was made.');
            } else {
                if ($request->quantity > $store_item->quantity) {
                    return redirect()
                        ->back()
                        ->with('error', 'The entered quantity is more than in store requisition that was made.');
                }
            }

            if ($request->filled('serialNumbers')) {
                $rules['serialNumbers'] = 'array';
                $rules['serialNumbers.*'] = 'required|distinct|string|min:2|max:50';

                $validated = $request->validate($rules);

                $id = $store_item->id;

                $serial_numbers = SerialNumber::where('store_item_id', $id)
                    ->where('store_requisition_id', $request->requisition_id)
                    ->pluck('serial_number')
                    ->toArray();

                if (!empty(array_diff($validated['serialNumbers'], $serial_numbers))) {
                    return redirect()
                        ->back()
                        ->with('error', 'The serial numbers do not match those on store requisition');
                }
            } else {
                $validated = $request->validate($rules);
            }

            $balance = $store_item->quantity - $validated['quantity'];

            // store in recovery_store_requisition_items
            $created = RecoveryStoreRequisitionItem::create([
                'recovery_requisition_id' => $request->recovery_requisition_id,
                'item_name' => $validated['item_name'],
                'quantity' => $validated['quantity'],
                'status' => $validated['status'],
                'balance' => $balance
            ]);

            // Update RecoveryStore inventory
            $store = RecoveryStore::where('item_name', $validated['item_name'])->first();
            if ($store) {
                $current_quantity = $store->quantity;
                $store->update(['quantity' => $current_quantity + $validated['quantity']]);
            } else {
                RecoveryStore::create([
                    'item_name' => $validated['item_name'],
                    'quantity' => $validated['quantity'],
                ]);
            }

            // handle serial numbers
            if (isset($validated['serialNumbers'])) {
                $id = $created->id;

                foreach ($validated['serialNumbers'] as $serial) {
                    RecoveryStoreRequisitionItemSerialNumber::create([
                        'recovery_store_requisition_item_id' => $id,
                        'serial_numbers' => $serial,
                    ]);
                }

                $store = RecoveryStore::where('item_name', $validated['item_name'])->first();
                $id2 = $store->id;

                foreach ($validated['serialNumbers'] as $serial) {
                    RecoveryStoreSerialNumber::create([
                        'recovery_store_id' => $id2,
                        'serial_numbers' => $serial,
                    ]);
                }
            }


            return redirect()->route('recovery.items.index', ['recovery_requisition_id' => $request->recovery_requisition_id, 'requisition_id' => $request->requisition_id])
                ->with('success', 'Items added successfully');
        }*/
}

