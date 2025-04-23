<?php

namespace App\Http\Controllers;

use App\Models\ReturnsStore;
use App\Models\ReturnsStoreSerialNumber;
use Illuminate\Http\Request;

class ReturnsStoreSerialNumberController extends Controller
{
    public function index($returns_store_id)
    {
        $item = ReturnsStore::find($returns_store_id);

        $serials = ReturnsStoreSerialNumber::with(['item'])
            ->where('returns_store_id', $returns_store_id)
            ->get();

        return view('return.serial.index', [
            'serials' => $serials,
            'item_name' => $item->item_name
        ]);
    }
}
