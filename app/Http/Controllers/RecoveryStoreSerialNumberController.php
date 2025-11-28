<?php

namespace App\Http\Controllers;

use App\Models\RecoveryStore;
use App\Models\RecoveryStoreSerialNumber;
use Illuminate\Http\Request;

class RecoveryStoreSerialNumberController extends Controller
{
    public function index($recovery_store_id)
    {
        $item = RecoveryStore::find($recovery_store_id);

        $serials = RecoveryStoreSerialNumber::with(['item'])
            ->where('recovery_store_id', $recovery_store_id)
            ->get();

        return view('recovered.serial.index', [
            'serials' => $serials,
            'item_name' => $item->item_name
        ]);
    }
}

