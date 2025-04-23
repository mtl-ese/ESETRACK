<?php

namespace App\Http\Controllers;

use App\Models\ReturnsStore;
use App\Models\ReturnsStoreSerialNumber;
use App\Models\StoreRequisition;
use App\Models\StoreReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Redirect;

class StoreReturnController extends Controller
{
    public function index()
    {
        $stores = StoreReturn::latest()->with(['creator', 'old_creator'])->get();

        return view('returns.index', [
            'stores' => $stores
        ]);
    }
    public function create()
    {

        return view('returns.create');
    }

    public function store(Request $request)
    {
        //validate
        $validated = $request->validate([
            'requisition_id' => ['required', 'string'],
            'approved_by' => ['required', 'string'],
        ]);

        //check if a store requisition id of the returns exists
        $store_requisition_id = StoreRequisition::where('requisition_id', $validated['requisition_id'])->first();
        if (!$store_requisition_id) {
            return redirect()
                ->back()
                ->with('error', 'There is no such store requisition ID.');
        }

        //check if requisition_id already exists
        $requisition_id = StoreReturn::where('store_requisition_id', $validated['requisition_id'])->first();
        if ($requisition_id) {
            return redirect()
                ->back()
                ->with('error', 'store requisition ID on returns already exists.')
                ->withInput();
        }

        //get authenticated user
        $user = Auth::user();

        //store new store return
        StoreReturn::create([
            'store_requisition_id' => $validated['requisition_id'],
            'old_client' => $store_requisition_id->client_name,
            'location' => $store_requisition_id->location,
            'approved_by' => $validated['approved_by'],
            'was_approved_by' => $store_requisition_id->approved_by,
            'returned_on' => now(),
            'was_created_by' => $store_requisition_id->created_by,
            'created_by' => $user->id
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Store Return created successfully.');

    }

    public function search()
    {
        $results = StoreReturn::where('store_requisition_id', 'LIKE', '%' . request('q') . '%')
            ->orWhere('old_client', 'LIKE', '%' . request('q') . '%')
            ->get();

        if ($results->isEmpty()) {
            return redirect()->route('returns.index')->with('error', 'No items found');
        } else {

            return view('returns.search', [
                'stores' => $results,
                'query' => request('q')
            ]);
        }
    }

    public function destroy($requisition_id)
    {
        $storeReturn = StoreReturn::with('items.serial_numbers')->where('store_requisition_id', $requisition_id)->first();


        //just for checks, nothing personal
        if (!$storeReturn) {
            return redirect()
                ->back()
                ->with('error', 'Store Return not found');
        }

        //restore the items and their serial numbers
        foreach ($storeReturn->items as $item) {

            //get the item name and quantity
            $item_name = $item->item_name;
            $item_quantity = $item->quantity;

            //get the item in return stores
            $storeItem = ReturnsStore::where('item_name', $item_name)->first();

            $balance = $storeItem->quantity - $item_quantity;
            $storeItem->quantity = $balance;

            
            //remove its serial numbers if available
            if ($item->serial_numbers != null) {
                foreach ($item->serial_numbers as $serial_numbers) {
                    ReturnsStoreSerialNumber::where('serial_numbers', $serial_numbers->serial_numbers)->delete();
                }
            }
            $storeItem->save();
        }

        //delete the store return
        $storeReturn->delete();

        return redirect()
            ->route('returns.index')
            ->with('success', 'Store Return deleted successfully');
    }
}
