<?php

namespace App\Http\Controllers;

use App\Models\EmergencyReturn;
use App\Models\EmergencyReturnItem;

class EmergencyReturnItemController extends Controller
{
    /**
     * Display all emergency return items across all returns (materials index)
     */
    public function materialsIndex()
    {
        $returnItems = EmergencyReturnItem::with(['serial_numbers', 'returns.requisition'])
            ->latest()
            ->get();

        return view('emergency.return.materials.index', [
            'returnItems' => $returnItems
        ]);
    }

    /**
     * Display the list of items returned in a specific emergency return
     */
    public function index($emergency_return_id, $requisition_id)
    {
        $return = EmergencyReturn::findOrFail($emergency_return_id);
        $items = EmergencyReturnItem::where('emergency_return_id', $emergency_return_id)
            ->with('serial_numbers.itemSerialNumber')
            ->get();

        return view('emergency.return.items.index', [
            'return' => $return,
            'requisition_id' => $requisition_id,
            'items' => $items,
        ]);
    }
}
