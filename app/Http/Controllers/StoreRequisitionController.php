<?php

namespace App\Http\Controllers;

use App\Models\RecoveryStoreRequisition;
use App\Models\Store;
use App\Models\StoreItem;
use App\Models\StoreRequisition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

use function PHPUnit\Framework\isEmpty;

class StoreRequisitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stores = StoreRequisition::latest()->with(['creator'])->get();
        return view('storeReq.index', [
            'stores' => $stores,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('storeReq.create');
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        if (isset($request->location)) {
            //validate
            $validated = $request->validate([
                'requisition_id' => ['required', 'string'],
                'client_name' => ['required', 'string'],
                'location' => ['string'],
                'approved_by' => ['required', 'string']
            ]);
        } else {
            //validate
            $validated = $request->validate([
                'requisition_id' => ['required', 'string'],
                'client_name' => ['required', 'string'],
                'approved_by' => ['required', 'string']
            ]);
        }

        $location = $request->location ? $validated['location'] : null;

        //check if requisition_id already exists
        $requisition_id = StoreRequisition::where('requisition_id', $validated['requisition_id'])->first();
        if ($requisition_id) {
            return redirect()
                ->back()
                ->with('error', 'store requisition ID already exists.')
                ->withInput();
        }

        $requisition_id = RecoveryStoreRequisition::where('recovery_store_requisition_id', $validated['requisition_id'])->first();
        if ($requisition_id) {
            return redirect()
                ->back()
                ->with('error', 'There exists a recovery store requisition of same requisition ID.')
                ->withInput();
        }

        //get authenticated user
        $user = Auth::user();

        //store new store requisition
        StoreRequisition::create([
            'requisition_id' => $validated['requisition_id'],
            'client_name' => $validated['client_name'],
            'location' => $location,
            'approved_by' => $validated['approved_by'],
            'requested_on' => now(),
            'created_by' => $user->id
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Store Requisition created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($requisition_id)
    {
        // Fetch all items where the store_requisition_id matches
        $items = StoreItem::where('store_requisition_id', $requisition_id)->get();

        // Store requisition_id in session for future use if necessary
        session(['requisition_id' => $requisition_id]);

        // Pass the items to the view
        return view('storeReq.items.index', [
            'items' => $items,
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($requisition_id)
    {


        $record = StoreRequisition::with(['items', 'return'])->where('requisition_id', $requisition_id)->first();

        if ($record->return != null) {
            return redirect()
                ->back()
                ->with('error', 'Sorry. You cannot delete a store requisition that has been returned');
        }

        //update the quantity of the store items
        if ($record->items->count() > 0) {

            foreach ($record->items as $item) {
                $storeItem = Store::where('item_name', $item->item_name)->first();
                $storeItem->quantity += $item->quantity;
                $storeItem->save();
            }
        }

        //delete the store requisition
        $record->delete();
        return redirect()
            ->back()
            ->with('success', 'Store Requisition deleted successfully');
    }

    public function search()
    {
        $stores = StoreRequisition::with(['creator'])->where("requisition_id", "LIKE", "%" . request('q') . "%")
            ->orWhere("client_name", "LIKE", "%" . request("q") . "%")->get();

        if ($stores->isEmpty()) {
            return redirect()
                ->route('store.index')
                ->with('error', 'No records found')
                ->withInput();
        } else {
            return view("storeReq.search", [
                "stores" => $stores,
                'query' => request('q')
            ]);
        }
    }
}
