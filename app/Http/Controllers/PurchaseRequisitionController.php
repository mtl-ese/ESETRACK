<?php

namespace App\Http\Controllers;

use App\Models\PurchaseItem;
use App\Models\PurchaseRequisition;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;

class PurchaseRequisitionController extends Controller
{
    public function index()
    {

        $purchase = PurchaseRequisition::latest()->with(['creator'])->get();
        return view('purchaseReq.index', [
            'purchases' => $purchase,
        ]);
    }
    public function create()
    {
        return view('purchaseReq.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'requisition_id' => ['required', 'string'],
            'approved_by' => ['required', 'string']
        ]);
        //check if id already exists
        $id = PurchaseRequisition::where('requisition_id', $request->requisition_id)->first();
        if ($id) {
            return redirect()
                ->back()
                ->with('error', 'Sorry that purchase requisition ID already exists.')
                ->withInput();
        }
        $user = Auth::user();

        PurchaseRequisition::create([
            'requisition_id' => $request->requisition_id,
            'approved_by' => $request->approved_by,
            'created_by' => $user->id
        ]);

        return redirect()->route('dashboard')->with('success', 'Purchase Requisition created successfully.');


    }
    public function search()
    {
        $purchases = PurchaseRequisition::with(['creator'])->where("requisition_id", "LIKE", "%" . request('q') . "%")->get();

        if ($purchases->isEmpty()) {
            return redirect()
                ->route('purchase.index')
                ->with('error', 'No records found')
                ->withInput();
        } else {
            return view("purchaseReq.search", [
                "purchases" => $purchases,
                'query' => request('q')
            ]);
        }


    }

    public function show($requisition_id)
    {

        $items = PurchaseItem::where('purchase_requisition_id', $requisition_id)->get();
        session(['requisition_id' => $requisition_id]);

        return view('purchaseReq.items.index', [
            'items' => $items,
        ]);
    }

    public function destroy($requisition_id)
    {

        $record = PurchaseRequisition::with('acquired.items')->where('requisition_id', $requisition_id)->first();

        if (!$record->acquired) {
            $record->delete();
        } else {
            return redirect()->back()->with('error', 'Sorry, you cannot delete a purchase requisition that has already been acquired');
        }
        return redirect()->route('purchase.index')->with('success', $requisition_id . ' purchase requisition deleted succesfully');

    }
}
