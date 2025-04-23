<?php

namespace App\Http\Controllers;

use App\Models\EmergencyRequisition;
use App\Models\EmergencyRequisitionItem;
use App\Models\EmergencyRequisitionItemSerial;
use App\Models\ReturnsStore;
use App\Models\ReturnsStoreSerialNumber;
use App\Models\Store;
use Illuminate\Http\Request;

class EmergencyRequisitionItemController extends Controller
{
    public function index($requisition_id)
    {
        $items = EmergencyRequisitionItem::with('serial_numbers')->where('emergency_requisition_id', $requisition_id)->get();

        return view('emergency.items.index', [
            'items' => $items,
            'requisition_id' => $requisition_id
        ]);
    }

    public function create($requisition_id)
    {

        return view('emergency.items.create', [
            'requisition_id' => $requisition_id
        ]);
    }

    public function store(Request $request)
    {


        //check if id exists
        $id = EmergencyRequisition::where('requisition_id', $request->requisition_id)->first();
        if (!$id) {
            return redirect()
                ->back()
                ->with('error', 'Sorry, Emergency Requisition ID not valid.')
                ->withInput();
        }

        //check if item is already stored
        $item = EmergencyRequisitionItem::where('item_name', $request->item_name)->where('emergency_requisition_id', $request->requisition_id)->first();
        if ($item) {
            return redirect()
                ->back()
                ->with('error', $request->item_name . ' is already stored in this emergency requisition.')
                ->withInput();
        }
        //initialize the booleans
        $new = false;
        $old = false;
        $filled = false;

        //validate

        if ($request->filled('serialNumbers')) {
            $validated = $request->validate([
                'item_description' => ['required', 'string'],
                'quantity' => ['required', 'min:1'],
                'from' => ['required', 'in:stores,return stores'],
                'serialNumbers' => ['array'],
                'serialNumbers.*' => 'required|distinct|string|min:2|max:50'
            ]);

            $filled = true;
        } else {
            $validated = $request->validate([
                'item_description' => ['required', 'string'],
                'quantity' => ['required', 'min:1'],
                'from' => ['required', 'in:stores,return stores']
            ]);
        }


        // check if item is new or old based on the from value.
        if ($validated['from'] == 'stores') {
            $new = true;
        }
        if ($validated['from'] == 'return stores') {
            $old = true;
        }

        //if new, check if that item is available in stores with right quantity
        if ($new) {
            $item = Store::where('item_name', $validated['item_description'])->first();
            if ($item == null) {
                return redirect()
                    ->back()
                    ->with('error', $validated['item_description'] . ' is not available in stores')
                    ->withInput();
            }

            $balance = $item->quantity - $validated['quantity'];
            if ($balance < 0) {
                return redirect()
                    ->back()
                    ->with('error', 'The requested quantity is more than what is available in stores. Current quantity for ' . $validated['item_description'] . ' is ' . $item->quantity)
                    ->withInput();
            }

            //store the item
            EmergencyRequisitionItem::create([
                'emergency_requisition_id' => $request->requisition_id,
                'item_name' => $validated['item_description'],
                'quantity' => $validated['quantity'],
                'from' => $validated['from'],
                'same_to_return' => $request->will_return === 'on' ? 1 : 0,
            ]);


            //if item has serial numbers
            if ($filled) {


                //get the recent created item
                $emergencyItem = EmergencyRequisitionItem::where('item_name', $validated['item_description'])
                    ->where('emergency_requisition_id', $request->requisition_id)
                    ->first();

                foreach ($validated['serialNumbers'] as $serial) {

                    //add the serial numbers to the item
                    EmergencyRequisitionItemSerial::create([
                        'item_id' => $emergencyItem->id,
                        'serial_number' => $serial
                    ]);
                }
            }


            //update in stores
            $item->update([
                'quantity' => $balance
            ]);
        }

        //if old, check if item is available in return stores
        if ($old) {

            $item = ReturnsStore::where('item_name', $validated['item_description'])->first();
            if ($item == null) {
                return redirect()
                    ->back()
                    ->with('error', $validated['item_description'] . ' is not available in return stores')
                    ->withInput();
            }

            $balance = $item->quantity - $validated['quantity'];

            if ($balance < 0) {
                return redirect()
                    ->back()
                    ->with('error', 'The requested quantity is more than what is available in return stores.
                 Current quantity for ' . $validated['item_description'] . ' is ' . $item->quantity)
                    ->withInput();
            }

            //update in return stores
            $item->quantity = $balance;

            //if item has serial numbers, update the return stores accordingly
            if ($filled) {

                //check if the provided serial numbers are available
                $serial_numbers = ReturnsStoreSerialNumber::where('returns_store_id', $item->id)
                    ->pluck('serial_numbers')
                    ->toArray();

                if (empty(array_diff($validated['serialNumbers'], $serial_numbers))) {
                } else {
                    return redirect()
                        ->back()
                        ->with('error', 'The serial number(s) do not match those in return stores for ' . $validated['item_description'])
                        ->withInput();
                }

                $item->save();

                //store the item
                EmergencyRequisitionItem::create([
                    'emergency_requisition_id' => $request->requisition_id,
                    'item_name' => $validated['item_description'],
                    'quantity' => $validated['quantity'],
                    'from' => $validated['from'],
                    'same_to_return' => $request->will_return === 'on' ? 1 : 0,

                ]);
                //get the recent created item
                $item = EmergencyRequisitionItem::where('item_name', $validated['item_description'])
                    ->where('emergency_requisition_id', $request->requisition_id)
                    ->first();

                foreach ($validated['serialNumbers'] as $serial) {

                    //add the serial numbers to the item
                    EmergencyRequisitionItemSerial::create([
                        'item_id' => $item->id,
                        'serial_number' => $serial
                    ]);

                    //remove the serial numbers from return stores
                    ReturnsStoreSerialNumber::where('serial_numbers', $serial)->delete();
                }
            }
        }

        return redirect()
            ->route('emergencyItemsIndex', $request->requisition_id)
            ->with('success', 'Item added successfully');
    }
}