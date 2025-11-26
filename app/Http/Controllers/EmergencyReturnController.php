<?php

namespace App\Http\Controllers;

use App\Models\EmergencyRequisition;
use App\Models\EmergencyRequisitionItem;
use App\Models\ReturnsStore;
use App\Models\ReturnsStoreSerialNumber;
use App\Models\Store;
use Illuminate\Http\Request;

class EmergencyReturnController extends Controller
{

    public function create()
    {
        return view('emergency.return.create');
    }
    public function store(Request $request)
    {
        //validate the requisition id
        $validated = $request->validate([
            'requisition_id' => 'string'
        ]);

        //check if that id exist in emergency requisitions
        $identity = EmergencyRequisition::with('items.serial_numbers')->where('requisition_id', $validated['requisition_id'])->first();
        if ($identity === null) {
            return redirect()
                ->back()
                ->with('error', 'Sorry, ' . $validated['requisition_id'] . ' does not appear as a valid emergency requisition id.')
                ->withInput();
        }

        //check if the emergency requisition has no items
        if (!$identity->items->count() > 0) {
            return redirect()
                ->back()
                ->with('error', 'Emergency requisition has no items')
                ->withInput();
        }

        //check if that id is already returned
        $returned = $identity->returned_on;

        if ($returned) {
            return redirect()
                ->back()
                ->with('error', $validated['requisition_id'] . ' has already been returned');
        }

        return view('emergency.return.show', [
            'identity' => $identity,
            'items' => $identity->items
        ]);
    }
    public function confirm(Request $request)
    {
        $identity = EmergencyRequisition::with('items.serial_numbers')->where('requisition_id', $request->requisition_id)->first();
        foreach ($identity->items as $item) {
            //if an item is new, just add to stores
            if ($item->same_to_return === 0) {
                $storeItem = Store::where('item_name', $item->item_name)->first();

                if ($storeItem) {
                    $storeItem->quantity += $item->quantity;
                    $storeItem->save();
                } else {
                    Store::create([
                        'item_name' => $item->item_name,
                        'quantity' => $item->quantity,
                    ]);
                }
            } else {
                //if an item is the same,add it to return stores then  check if it has serial numbers if yes add them, if not add it to return store

                $returnItem = ReturnsStore::where('item_name', $item->item_name)->first();

                if (!$returnItem) {
                    ReturnsStore::create([
                        'item_name' => $item->item_name,
                        'quantity' => $item->quantity,
                    ]);
                } else {
                    $returnItem->quantity += $item->quantity;
                    $returnItem->save();
                }

                //get the recent stored or updated item
                $recentItem = ReturnsStore::where('item_name', $item->item_name)->first();


                //add serial numbers if present
                if ($item->serial_numbers) {
                    foreach ($item->serial_numbers as $serial) {
                        ReturnsStoreSerialNumber::create([
                            'returns_store_id' => $recentItem->id,
                            'serial_numbers' => $serial->serial_number,
                        ]);
                    }
                }
            }
        }
        //mark the emergency requisition as returned
        $identity->returned_on = now();
        $identity->save();

        //redirect to the requisition page with success message
        return redirect()
            ->route('dashboard')
            ->with('success', 'Emergency requisition has been returned successfully');
    }
}