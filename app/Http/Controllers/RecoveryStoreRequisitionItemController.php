<?php

namespace App\Http\Controllers;

use App\Models\RecoveredItem;
use App\Models\RecoveryStoreRequisitionItem;
use App\Models\RecoveryStoreRequisitionItemSerialNumber;
use App\Models\ReturnsStore;
use App\Models\ReturnsStoreSerialNumber;
use Illuminate\Http\Request;
use League\Csv\Serializer\CastToArray;

class RecoveryStoreRequisitionItemController extends Controller
{
    public function index($requisition_id)
    {

        $items = RecoveryStoreRequisitionItem::where('recovery_requisition_id', $requisition_id)->get();
        return view('recovery.items.index', [
            'requisition_id' => $requisition_id,
            'items' => $items
        ]);
    }

    public function create($requisition_id)
    {

        return view('recovery.items.create', [
            'requisition_id' => $requisition_id
        ]);
    }

    public function store(Request $request)
    {
        // Set up basic validation rules
        $rules = [
            'item_name' => 'required|string',
            'quantity' => 'required|integer|min:1'

        ];

        //check if item is already entered
        $item = RecoveryStoreRequisitionItem::where('item_name', $request->item_name)
            ->where('recovery_requisition_id', $request->requisition_id)->first();

        if ($item) {
            return redirect()
                ->back()
                ->with('error', $request->item_name . ' is already entered')
                ->withInput();
        }
        // If serial numbers are provided, validate them
        if ($request->filled('serialNumbers')) {
            $rules['serialNumbers'] = 'array';
            $rules['serialNumbers.*'] = 'required|distinct|string|min:2|max:50';

            $validated = $request->validate($rules);

            //check if the provided serials are available in return stores
            $serial_numbers = ReturnsStoreSerialNumber::all()->pluck('serial_numbers')->toArray();

            if (empty(array_diff($validated['serialNumbers'], $serial_numbers))) {

            } else {
                return redirect()
                    ->back()
                    ->with('error', 'The serial numbers do not match those in return stores')
                    ->withInput();
            }
        }


        $validated = $request->validate($rules);


        // Find the  item by in returns store item name
        $storeItem = ReturnsStore::where('item_name', $validated['item_name'])->first();

        if (!$storeItem) {
            return redirect()->back()->with('error', 'No such item in returns storage');
        }

        // Assign the quantity to a new variable
        $requestedQuantity = $validated['quantity'];

        //calculate the remaining quantity
        $remainingQuantity = $storeItem->quantity - $requestedQuantity;

        //check if the remaining quantity is not less than zero
        if ($remainingQuantity < 0) {
            return redirect()->back()->with(
                'error',
                'There are only ' . $storeItem->quantity . ' ' . $storeItem->item_name . ' in recovered items stores.'
            );
        }

        // Store store_requisition_id, item name and quantity
        RecoveryStoreRequisitionItem::create([
            'recovery_requisition_id' => $request->requisition_id,
            'item_name' => $validated['item_name'],
            'quantity' => $validated['quantity']
        ]);

        // Process the request based on whether serial numbers are present.
        if (isset($validated['serialNumbers'])) {


            //get the id of stored item
            $item = RecoveryStoreRequisitionItem::where('item_name', $validated['item_name'])->first();
            $id = $item->id;

            //store serial numbers for the item and remove them from return stores
            foreach ($validated['serialNumbers'] as $serial) {
                RecoveryStoreRequisitionItemSerialNumber::create([
                    'item_id' => $id,
                    'serial_number' => $serial,
                ]);

                ReturnsStoreSerialNumber::where('serial_numbers', $serial)->delete();


            }

            // Update the store quantity after the above operation succeeds
            $storeItem->update(['quantity' => $remainingQuantity]);

            //redirect to show the created item
            return redirect()->route('recovery-items.index', $request->requisition_id)
                ->with('success', 'Item added successfully');

        }
    }
}