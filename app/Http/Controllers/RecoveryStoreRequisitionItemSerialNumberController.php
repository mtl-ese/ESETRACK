<?php

namespace App\Http\Controllers;

use App\Models\RecoveryStoreRequisitionItemSerialNumber;
use Illuminate\Http\Request;

class RecoveryStoreRequisitionItemSerialNumberController extends Controller
{
    public function index($item_id, $requisition_id)
    {

        $serial_numbers = RecoveryStoreRequisitionItemSerialNumber::with('item')->where('item_id', $item_id)->get();
        $item_name = $serial_numbers[0]->item->item_name;

        return view('recovery.items.serials.index', [
            'serial_numbers' => $serial_numbers,
            'item_name' => $item_name,
            'requisition_id' => $requisition_id

        ]);
    }
}
