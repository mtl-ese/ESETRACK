<?php

namespace App\Http\Controllers;

use App\Models\RecoveredItemSerialNumber;
use Illuminate\Http\Request;

class RecoveredItemSerialNumberController extends Controller
{
    public function index($store_return_id, $requisition_id)
    {
        $serial_numbers = RecoveredItemSerialNumber::with('item')->where('recovered_item_id', $store_return_id)->get();

        return view('returns.items.serials.index', [
            'serial_numbers' => $serial_numbers,
            'store_return_id' => $store_return_id,
            'requisition_id' => $requisition_id
        ]);
    }
}
