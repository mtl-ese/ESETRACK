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

    public function materialsIndex()
    {
        $purchaseItems = PurchaseItem::with('requisition')->latest()->get();
        return view('purchaseReq.materials.index', [
            'purchaseItems' => $purchaseItems
        ]);
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

        // Handle batch submission
        if ($request->has('items')) {
            $items = json_decode($request->items, true);

            if (!$items || !is_array($items)) {
                return redirect()->back()->with('error', 'No items to process');
            }

            $normalizedNames = [];
            foreach ($items as $itemData) {
                $rules = [
                    'item_name' => 'required|string',
                    'quantity' => 'required|integer|min:1'
                ];

                $validated = [
                    'item_name' => $itemData['item_name'],
                    'quantity' => $itemData['quantity'],
                ];

                $nameKey = mb_strtolower(trim($validated['item_name']));
                if (in_array($nameKey, $normalizedNames, true)) {
                    return redirect()->back()->with('error', 'Duplicate materials detected in submission.');
                }
                $normalizedNames[] = $nameKey;

                $existing = PurchaseItem::where('purchase_requisition_id', $request->requisition_id)
                    ->whereRaw('LOWER(item_description) = ?', [$nameKey])
                    ->exists();

                if ($existing) {
                    return redirect()->back()->with('error', 'This material already exists on the requisition.');
                }

                PurchaseItem::create([
                    'purchase_requisition_id' => $request->requisition_id,
                    'item_description' => $validated['item_name'],
                    'quantity' => $validated['quantity']
                ]);
            }

            return redirect()->route('purchase.index')
                ->with('success', 'Purchase requisition created successfully');
        }
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
