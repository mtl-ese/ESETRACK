<?php

namespace App\Http\Controllers;

use App\Models\RecoveredItem;
use App\Models\RecoveredItemSerialNumber;
use App\Models\ReturnsStore;
use App\Models\ReturnsStoreSerialNumber;
use App\Models\SerialNumber;
use App\Models\StoreItem;
use Illuminate\Http\Request;

class RecoveredItemController extends Controller
{
    public function index($store_return_id, $requisition_id)
    {
        $items = RecoveredItem::where('store_return_id', $store_return_id)->get();

        return view('returns.items.index', [
            'items' => $items,
            'store_return_id' => $store_return_id,
            'requisition_id' => $requisition_id
        ]);
    }

    public function create($store_return_id, $requisition_id)
    {

        return view('returns.items.create', [
            'store_return_id' => $store_return_id,
            'requisition_id' => $requisition_id
        ]);
    }

    public function store(Request $request)
    {
        // Set up basic validation rules
        $rules = [
            'item_name' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|string|max:50'

        ];

        //check if item is already stored
        $item = RecoveredItem::where('item_name', $request->item_name)->where('store_return_id', $request->store_return_id)->first();

        if ($item) {
            return redirect()
                ->back()
                ->with('error', $request->item_name . ' is already stored in this store return.');
        }

        $item = StoreItem::where('item_name', $request->item_name)->where('store_requisition_id', $request->requisition_id)->first();
        if (!$item) {
            return redirect()
                ->back()
                ->with('error', $request->item_name . ' is not found in the store requisition that was made.');
        } else {

            if ($request->quantity > $item->quantity) {
                return redirect()
                    ->back()
                    ->with('error', 'The entered quantity is more than in store requsition that was made.');

            }
        }

        // If serial numbers are provided, validate them; otherwise, validate the other fields.
        if ($request->filled('serialNumbers')) {
            $rules['serialNumbers'] = 'array';
            $rules['serialNumbers.*'] = 'required|distinct|string|min:2|max:50';

            $validated = $request->validate($rules);

            //get id of stored_item
            $id = $item->id;


            $serial_numbers = SerialNumber::where('store_item_id', $id)
                ->where('store_requisition_id', $request->requisition_id)
                ->pluck('serial_number')
                ->toArray();


            //check if the serial numbers provided match those that were installed at the customer
            if (empty(array_diff($validated['serialNumbers'], $serial_numbers))) {

            } else {
                return redirect()
                    ->back()
                    ->with('error', 'The serial numbers do not match those on store requisition');
            }

        } else {
            $validated = $request->validate($rules);

        }
        //find balance
        $balance = $item->quantity - $validated['quantity'];

        //store in recovered items
        RecoveredItem::create([
            'store_return_id' => $request->store_return_id,
            'item_name' => $validated['item_name'],
            'quantity' => $validated['quantity'],
            'status' => $validated['status'],
            'balance' => $balance
        ]);

        // Find the store item by item name in returns store
        $storeItem = ReturnsStore::where('item_name', $validated['item_name'])->first();

        //if item name is found, update its quantity
        if ($storeItem) {
            $current_quantity = $storeItem->quantity;
            $storeItem->update(['quantity' => $current_quantity + $validated['quantity']]);
        }

        //if not found, create a new one
        else {
            ReturnsStore::create([
                'item_name' => $validated['item_name'],
                'quantity' => $validated['quantity'],
            ]);
        }

        //if item has serial numbers add them, else leave it like that
        if (isset($validated['serialNumbers'])) {

            //get the id of recovered item
            $item = RecoveredItem::where('item_name', $validated['item_name'])
                ->where('store_return_id', $request->store_return_id)
                ->first();
            $id = $item->id;

            //store serial numbers for the recovered item
            foreach ($validated['serialNumbers'] as $serial) {
                RecoveredItemSerialNumber::create([
                    'recovered_item_id' => $id,
                    'serial_numbers' => $serial,
                ]);
            }

            //get the id of stored item
            $item = ReturnsStore::where('item_name', $validated['item_name'])->first();
            $id = $item->id;

            //store serial numbers for the item
            foreach ($validated['serialNumbers'] as $serial) {
                ReturnsStoreSerialNumber::create([
                    'returns_store_id' => $id,
                    'serial_numbers' => $serial,
                ]);
            }
        }

        //redirect to show the created item
        return redirect()->route('recovered-items.index', ['store_return_id' => $request->store_return_id, 'requisition_id' => $request->requisition_id])
            ->with('success', 'Item added successfully');
    }


}