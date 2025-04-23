<?php

namespace App\Http\Controllers;

use App\Models\EmergencyRequisitionItem;
use App\Models\EmergencyRequisitionItemSerial;
use Illuminate\Http\Request;

class EmergencyRequisitionItemSerialController extends Controller
{
    public function index($item_id)
    {
        $serial_numbers = EmergencyRequisitionItemSerial::where('item_id', $item_id)->get();
        $item = EmergencyRequisitionItem::where('id', $item_id)->first();
        return view('emergency.items.serials.index', [
            'serial_numbers' => $serial_numbers,
            'item_name' => $item->item_name,
            'requisition_id' => $item->emergency_requisition_id
        ]);

        
    }
}
