<?php

namespace App\Http\Controllers;

use App\Models\EmergencyRequisition;
use App\Models\ReturnsStore;
use App\Models\ReturnsStoreSerialNumber;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmergencyRequisitionController extends Controller
{
    public function index()
    {
        $emergencies = EmergencyRequisition::latest()->with(['creator'])->get();

        return view('emergency.index', [
            'emergencies' => $emergencies
        ]);
    }

    public function create()
    {
        return view('emergency.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'requisition_id' => ['required', 'string'],
            'initiator' => ['required', 'string'],
            'department' => ['required', 'string'],
            'approved_by' => ['required', 'string']
        ]);

        //check if id already exists
        $id = EmergencyRequisition::where('requisition_id', $validated['requisition_id'])->first();
        if ($id) {
            return redirect()
                ->back()
                ->with('error', 'Sorry, Emergency Requisition ID already exists.')
                ->withInput();
        }

        $user = Auth::user();

        EmergencyRequisition::create([
            'requisition_id' => $validated['requisition_id'],
            'approved_by' => $validated['approved_by'],
            'initiator' => $validated['initiator'],
            'department' => $validated['department'],
            'created_by' => $user->id,
            'returned_on' => null
        ]);

        return redirect()->route('dashboard')->with('success', 'Emergency Requisition created successfully.');
    }

    public function search()
    {
        $emergencies = EmergencyRequisition::with(['creator'])->where("requisition_id", "LIKE", "%" . request('q') . "%")->get();

        if ($emergencies->isEmpty()) {
            return redirect()->route('emergencyIndex')->with('error', 'No records found');
        } else {
            return view("emergency.search", [
                "emergencies" => $emergencies,
                'query' => request('q')
            ]);
        }
    }

    public function destroy($requisition_id)
    {
        $emergency = EmergencyRequisition::with(['items.serial_numbers', 'return'])
            ->where('requisition_id', $requisition_id)
            ->first();


        //just for checks, nothing personal
        if (!$emergency) {
            return redirect()
                ->back()
                ->with('error', 'Emergency requisition not found');
        }

        //check if it is returned
        if ($emergency->returned_on) {
            return redirect()
                ->back()
                ->with('error', 'Sorry, Emergency requisition already returned');
        }

        //restore its items and their serial numbers
        foreach ($emergency->items as $item) {

            //do this if item is from return stores
            if ($item->from == 'return stores') {

                //restore the serial numbers
                foreach ($item->serial_numbers as $serial) {

                    //get the item id from return stores
                    $item_id = ReturnsStore::where('item_name', $item->item_name)->first()->id;

                    //return the serial numbers
                    ReturnsStoreSerialNumber::create([
                        'returns_store_id' => $item_id,
                        'serial_numbers' => $serial->serial_number
                    ]);
                }

                //update item quantity in return stores
                $returnStoreItem = ReturnsStore::where('item_name', $item->item_name)->first();
                $returnStoreItem->quantity += $item->quantity;
                $returnStoreItem->save();

                //delete the item
                $item->delete();
            } else {
                $storeItem = Store::where('item_name', $item->item_name)->first();
                $storeItem->quantity += $item->quantity;
                $storeItem->save();
            }
        }
        $emergency->delete();
        return redirect()->route('emergencyIndex')->with('success', $requisition_id . ' emergency requisition deleted succesfully');
    }
}