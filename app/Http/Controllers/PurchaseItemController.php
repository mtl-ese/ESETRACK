<?php

namespace App\Http\Controllers;

use App\Models\PurchaseItem;
use Illuminate\Http\Request;

class PurchaseItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('purchaseReq.items.create', [
            'requisition_id' => request('requisition_id')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_description' => ['required', 'string'],
            'quantity' => ['required', 'min:1']
        ]);

        PurchaseItem::create([
            'purchase_requisition_id' => $request->requisition_id,
            'item_description' => $request->item_description,
            'quantity' => $request->quantity
        ]);

        return redirect()->route('purchase.show', $request->requisition_id)->with('success', 'Item added successfully');


    }

   
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $item = PurchaseItem::find($id);

        return view('purchaseReq.items.edit', [
            'item' => $item,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'item_description' => ['required', 'string'],
            'quantity' => ['required']
        ]);


        $purchaseItem = PurchaseItem::where('id', $request->id)->first();
        $purchaseItem->update([
            'item_description' => $validated['item_description'],
            'quantity' => $validated['quantity']
        ]);

        return redirect()
            ->route('purchase.show', $request->requisition_id)
            ->with('success', 'Item updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
