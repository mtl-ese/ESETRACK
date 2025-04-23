<?php

namespace App\Http\Controllers;

use App\Models\SerialNumber;
use App\Models\Store;
use App\Models\StoreItem;
use Illuminate\Http\Request;

class StoreItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('storeReq.items.create', [
            'requisition_id' => request('requisition_id')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Set up basic validation rules
        $rules = [
            'item_name' => 'required|string',
            'quantity' => 'required|integer|min:1'

        ];

        // If serial numbers are provided, validate them; otherwise, validate the quantity field.
        if ($request->filled('serialNumbers')) {
            $rules['serialNumbers'] = 'array';
            $rules['serialNumbers.*'] = 'required|distinct|string|min:2|max:50';
        } else {
            $rules['quantity'] = 'required|integer|min:1';
        }

        $validated = $request->validate($rules);


        // Find the store item by item name
        $storeItem = Store::where('item_name', $validated['item_name'])->first();

        if (!$storeItem) {
            return redirect()
                ->back()
                ->with('error', 'No such item in stores')
                ->withInput();
        }

        // Assign the quantity to a new variable
        $requestedQuantity = $validated['quantity'];

        //calculate the remaining quantity
        $remainingQuantity = $storeItem->quantity - $requestedQuantity;

        //check if the remaining quantity is not less than zero
        if ($remainingQuantity < 0) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'There are only ' . $storeItem->quantity . ' ' . $storeItem->item_name . ' in stores.'
                )->withInput();
        }

        // Store store_requisition_id, item name and quantity
        StoreItem::create([
            'store_requisition_id' => $request->requisition_id,
            'item_name' => $validated['item_name'],
            'quantity' => $validated['quantity']
        ]);

        // Process the request based on whether serial numbers are present.
        if (isset($validated['serialNumbers'])) {

            //get the id of stored item
            $item = StoreItem::where('item_name', $validated['item_name'])
            ->where('store_requisition_id',$request->requisition_id)->first();
            $id = $item->id;

            //store serial numbers for the item
            foreach ($validated['serialNumbers'] as $serial) {
                $record = SerialNumber::where('serial_number', $serial)->first();
                if ($record) {
                    $item->delete();
                    return redirect()
                        ->back()
                        ->with('error', 'Serial number ' . $serial . ' already exists in the system.')
                        ->withInput();
                }
                SerialNumber::create([
                    'store_item_id' => $id,
                    'store_requisition_id' => $request->requisition_id,
                    'serial_number' => $serial,
                ]);
            }
        }

        // Update the store quantity after the above operation succeeds
        $storeItem->update(['quantity' => $remainingQuantity]);

        //redirect to show the created item
        return redirect()->route('store.show', $request->requisition_id)
            ->with('success', 'Item added successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show($requisition_id,$id)
    {
        //get item name
        $item = StoreItem::where('store_requisition_id',$requisition_id)->first();
        $item_name = $item->item_name;
        $requisition_id = $item->store_requisition_id;


        //get serial numbers with matching id
        $serial_numbers = SerialNumber::where('store_item_id', $id)->where('store_requisition_id',$item->store_requisition_id)->get();

        //return the serial numbers and item name to item.show view
        return view('storeReq.items.serials.index', [
            'serial_numbers' => $serial_numbers,
            'item_name' => $item_name,
            'requisition_id' => $requisition_id
        ]);
    }


}
