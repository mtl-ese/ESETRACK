<?php

namespace App\Http\Controllers;

use App\Models\ReturnsStore;
use Illuminate\Http\Request;

class ReturnsStoreController extends Controller
{
    public function index()
    {
        $items = ReturnsStore::all();

        return view('return.index', [
            'items' => $items
        ]);
    }

    public function search()
    {
        $results = ReturnsStore::where('item_name', 'LIKE', '%' . request('q') . '%')->get();
        if ($results->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'No items found');
        } else {
            return view('return.search', [
                'items' => $results,
                'query' => request('q')
            ]);
        }
    }

}
