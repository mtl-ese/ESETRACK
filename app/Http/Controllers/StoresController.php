<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreItem;
use Illuminate\Http\Request;

class StoresController extends Controller
{

    public function index()
    {
        $items = Store::latest()->get();

        return view('stores.index', [
            'items' => $items,
        ]);
    }
    public function search()
    {
        $items = Store::where("item_name", "LIKE", "%" . request('q') . "%")->get();

        if ($items->isEmpty()) {
            return redirect()->route('stores.index')->with('error', 'No items found');
        } else {

            return view('stores.results', [
                'items' => $items,
                'query' => request('q')
            ]);
        }
    }
}
