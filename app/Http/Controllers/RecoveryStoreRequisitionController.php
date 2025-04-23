<?php

namespace App\Http\Controllers;

use App\Models\RecoveryStoreRequisition;
use App\Models\ReturnsStore;
use App\Models\ReturnsStoreSerialNumber;
use App\Models\StoreRequisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecoveryStoreRequisitionController extends Controller
{
    public function index()
    {
        $recoveries = RecoveryStoreRequisition::latest()->with(['creator'])->get();

        return view('recovery.index', [
            'recoveries' => $recoveries
        ]);
    }
    public function create()
    {
        return view('recovery.create');
    }

    public function store(Request $request)
    {

        //validate
        $validated = $request->validate([
            'requisition_id' => ['required', 'string'],
            'client' => ['required', 'string'],
            'location' => ['required', 'string'],
            'approved_by' => ['required', 'string']
        ]);

        //check if requisition_id already exists
        $requisition_id = RecoveryStoreRequisition::where('recovery_store_requisition_id', $validated['requisition_id'])->first();
        if ($requisition_id) {
            return redirect()
                ->back()
                ->with('error', ' recovery store requisition ID already exists.');
        }

        $requisition_id = StoreRequisition::where('requisition_id', $validated['requisition_id'])->first();
        if ($requisition_id) {
            return redirect()
                ->back()
                ->with('error', 'There exists a store requisition of same requisition ID.')
                ->withInput();
        }
        //get authenticated user
        $user = Auth::user();

        //store new store requisition
        RecoveryStoreRequisition::create([
            'recovery_store_requisition_id' => $validated['requisition_id'],
            'client_name' => $validated['client'],
            'location' => $validated['location'],
            'approved_by' => $validated['approved_by'],
            'requested_on' => now(),
            'created_by' => $user->id
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Recovery Store Requisition created successfully.');
    }
    public function search()
    {
        $search = RecoveryStoreRequisition::where('recovery_store_requisition_id', 'LIKE', "%" . request('q') . "%")
            ->orWhere('client_name', 'LIKE', "%" . request('q') . "%")->get();
        if ($search->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'No records found')
                ->withInput();

        } else {
            return view('recovery.search', [
                'recoveries' => $search,
                'query' => request('q')
            ]);
        }

    }
    public function destroy($requisition_id)
    {

        $recovery = RecoveryStoreRequisition::with('items.serial_numbers')->where('recovery_store_requisition_id', $requisition_id)->first();


        //return the item and its serial numbers if present
        if ($recovery->items != null) {
            foreach ($recovery->items as $item) {

                //get item in return stores
                $returnItem = ReturnsStore::where('item_name', $item->item_name)->first();

                //calculate balance
                $balance = $returnItem->quantity + $item->quantity;

                //assign balance
                $returnItem->quantity = $balance;


                if ($item->serial_numbers != null) {
                    foreach ($item->serial_numbers as $serial_number) {
                        ReturnsStoreSerialNumber::create([
                            'returns_store_id' => $returnItem->id,
                            'serial_numbers' => $serial_number->serial_number
                        ]);
                    }
                }

                $returnItem->save();
            }
        }


        //delete the recovery
        $recovery->delete();

        return redirect()
            ->route('recovery.index')
            ->with('success', 'Recovery Store Requisition ' . $requisition_id . ' deleted successfully.');
    }
}
