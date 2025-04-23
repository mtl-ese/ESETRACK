<?php

namespace App\Http\Controllers;

use App\Models\EmergencyRequisition;
use App\Models\EmergencyRequisitionItem;
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
    public function confirm($requisition_id)
    {
        dd();
    }
}
