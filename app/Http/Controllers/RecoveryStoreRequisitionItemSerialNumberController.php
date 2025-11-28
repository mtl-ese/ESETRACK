<?php

namespace App\Http\Controllers;

use App\Models\RecoveryStoreRequisitionItemSerialNumber;
use App\Models\RecoveryStoreRequisitionItem;
use Illuminate\Http\Request;

class RecoveryStoreRequisitionItemSerialNumberController extends Controller
{
    public function index($item_id, $requisition_id)
    {
        $serial_numbers = RecoveryStoreRequisitionItemSerialNumber::where('item_id', $item_id)->get();
        $item = RecoveryStoreRequisitionItem::where('id', $item_id)->first();

        return view('recovery.items.serials.index', [
            'serial_numbers' => $serial_numbers,
            'item_name' => $item->item_name,
            'requisition_id' => $requisition_id
        ]);
    }
}
