<?php

namespace App\Http\Controllers;

use App\Models\EmergencyRequisition;
use App\Models\EmergencyRequisitionItem;
use App\Models\ReturnsStore;
use App\Models\ReturnsStoreSerialNumber;
use App\Models\Store;
use App\Models\EmergencyReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class EmergencyReturnController extends Controller
{

    public function create()
    {
        // Provide available emergency requisitions for the datalist (only those not yet returned and with items)
        $requisitions = EmergencyRequisition::with('items')
            ->whereNull('returned_on')
            ->get()
            ->filter(fn($r) => $r->items->count() > 0)
            ->sortBy('requisition_id')
            ->values();

        return view('emergency.return.create', compact('requisitions'));
    }
    public function store(Request $request)
    {
        //validate the requisition id
        $validated = $request->validate([
            'requisition_id' => 'required|string',
            // the form field is `return_date` (not `requisition_date`) — require it here
            'return_date' => ['required', 'date', 'before_or_equal:today'],
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


        // Pass the chosen return_date through to confirmation view so the confirmation form includes it
        return view('emergency.return.show', [
            'identity' => $identity,
            'items' => $identity->items,
            'return_date' => $validated['return_date'] ?? null,
        ]);
    }
    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'requisition_id' => 'required|string|exists:emergency_requisitions,requisition_id',
            'return_date' => ['required', 'date', 'before_or_equal:today'],
        ]);

        $identity = EmergencyRequisition::with('items.serial_numbers')->where('requisition_id', $validated['requisition_id'])->firstOrFail();

        try {
            DB::transaction(function () use ($identity) {
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
            });

            // Persist the supplied return date on the requisition
            $identity->returned_on = $validated['return_date'];
            $identity->save();

            // Record the return in the emergency_returns table for history
            EmergencyReturn::create([
                'emergency_requisition_id' => $identity->requisition_id,
                'returned_on' => $validated['return_date'],
            ]);

        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Could not complete return. Please contact technical support.');
        }

        //redirect to the requisition page with success message
        return redirect()
            ->route('return.index')
            ->with('success', 'Emergency requisition has been returned successfully');
    }
}