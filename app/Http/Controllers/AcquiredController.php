<?php

namespace App\Http\Controllers;

use App\Models\Acquired;
use App\Models\AcquiredItem;
use App\Models\PurchaseItem;
use App\Models\PurchaseRequisition;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcquiredController extends Controller
{

    public function index()
    {
        //get all records in acquireds table
        $purchases = Acquired::latest()->with('items')->get();

        //return them to acquired.index view
        return view('acquired.index', [
            'purchases' => $purchases
        ]);
    }

    public function materialsIndex()
    {
        $acquiredItems = AcquiredItem::with('acquired')->latest()->get();
        return view('acquired.materials.index', [
            'acquiredItems' => $acquiredItems
        ]);
    }

    public function create()
    {
        // Get requisitions with unfulfilled items
        $requisitions = PurchaseRequisition::whereHas('items', function ($query) {
            $query->whereRaw('quantity > (
                SELECT COALESCE(SUM(acquired_items.quantity), 0) 
                FROM acquired_items 
                JOIN acquireds ON acquired_items.acquired_id = acquireds.id 
                WHERE acquireds.purchase_requisition_id = purchase_items.purchase_requisition_id 
                AND acquired_items.purchase_item_id = purchase_items.id
            )');
        })->get();

        return view('acquired.create', compact('requisitions'));
    }


    public function loadMaterials(Request $request)
    {
        $request->validate([
            'requisition_id' => 'required|exists:purchase_requisitions,requisition_id'
        ]);

        $requisition = PurchaseRequisition::where('requisition_id', $request->requisition_id)
            ->with('items')
            ->first();

        if (!$requisition) {
            return response()->json([
                'success' => false,
                'message' => 'Requisition not found'
            ]);
        }

        $materialsWithBalance = [];

        foreach ($requisition->items as $item) {
            // Calculate total acquired for this specific item
            $totalAcquired = AcquiredItem::where('purchase_item_id', $item->id)->sum('quantity');
            $balance = $item->quantity - $totalAcquired;

            if ($balance > 0) {
                $materialsWithBalance[] = [
                    'id' => $item->id,
                    'description' => $item->item_description,
                    'requested' => $item->quantity,
                    'acquired' => $totalAcquired,
                    'balance' => $balance
                ];
            }
        }

        if (empty($materialsWithBalance)) {
            return response()->json([
                'success' => false,
                'message' => 'All materials for this requisition have been fully acquired.'
            ]);
        }

        return response()->json([
            'success' => true,
            'materials' => $materialsWithBalance,
            'requisition' => $requisition
        ]);
    }
    //Batch processing
    public function store(Request $request)
    {
        $request->validate([
            'requisition_id' => 'required|exists:purchase_requisitions,requisition_id',
            'quantities' => 'required|array',
            'quantities.*' => 'nullable|integer|min:1'
        ]);

        // Check if at least one quantity is provided
        $hasQuantities = false;
        foreach ($request->quantities as $quantity) {
            if ($quantity && $quantity > 0) {
                $hasQuantities = true;
                break;
            }
        }

        if (!$hasQuantities) {
            return redirect()->back()
                ->with('error', 'Please enter at least one quantity to acquire materials.')
                ->withInput();
        }

        try {
            DB::transaction(function () use ($request) {
                // Create new acquisition batch
                $acquired = Acquired::create([
                    'purchase_requisition_id' => $request->requisition_id
                ]);

                foreach ($request->quantities as $itemId => $quantity) {
                    if ($quantity && $quantity > 0) {
                        $purchaseItem = PurchaseItem::find($itemId);

                        // Validate quantity doesn't exceed balance
                        $totalAcquired = AcquiredItem::whereHas('acquired', function ($query) use ($purchaseItem) {
                            $query->where('purchase_requisition_id', $purchaseItem->purchase_requisition_id);
                        })->where('purchase_item_id', $itemId)->sum('quantity');

                        $balance = $purchaseItem->quantity - $totalAcquired;

                        if ($quantity > $balance) {
                            throw new \Exception("Quantity for {$purchaseItem->item_description} exceeds available balance of {$balance}");
                        }

                        AcquiredItem::create([
                            'acquired_id' => $acquired->id,
                            'purchase_item_id' => $itemId,
                            'item_description' => $purchaseItem->item_description,
                            'quantity' => $quantity
                        ]);

                        // Update Store table
                        $existingItem = Store::where('item_name', $purchaseItem->item_description)->first();
                        if (!$existingItem) {
                            Store::create([
                                'item_name' => $purchaseItem->item_description,
                                'quantity' => $quantity
                            ]);
                        } else {
                            $existingItem->update([
                                'quantity' => $existingItem->quantity + $quantity
                            ]);
                        }
                    }
                }
            });

            return redirect()->route('acquired.index')
                ->with('success', 'Materials acquired successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not acquire due to internal server error. please consult technical support.')->withInput(['items' => $request->items]);
        }
    }

    public function editForm($id)
    {
        $acquired = Acquired::with(['items.purchaseItem', 'requisition'])->findOrFail($id);

        return view('acquired.edit', compact('acquired'));
    }

    public function updateAll(Request $request, $id)
    {
        $request->validate([
            'quantities' => 'required|array',
            'quantities.*' => 'nullable|integer|min:1'
        ]);

        $acquired = Acquired::with('items.purchaseItem')->findOrFail($id);

        // Detect no-change: if every submitted quantity equals existing acquired item quantity
        $submitted = array_map('intval', $request->quantities ?? []);
        $existing = $acquired->items->mapWithKeys(fn($it) => [$it->id => (int) $it->quantity])->toArray();

        $hasChanges = false;
        foreach ($submitted as $itemId => $qty) {
            $existingQty = $existing[$itemId] ?? null;
            if ($existingQty === null || $existingQty !== $qty) {
                $hasChanges = true;
                break;
            }
        }

        if (!$hasChanges) {
            return redirect()->back()->with('error', 'Nothing was changed. Please adjust at least one return quantity or serial selection.')->withInput();
        }

        try {
            DB::transaction(function () use ($request, $acquired) {
                foreach ($request->quantities as $itemId => $quantity) {
                    if ($quantity && $quantity > 0) {
                        $acquiredItem = $acquired->items->where('id', $itemId)->first();

                        if ($acquiredItem && $acquiredItem->purchaseItem) {
                            $purchaseItem = $acquiredItem->purchaseItem;

                            // Calculate total acquired for this purchase item (excluding current item)
                            $totalAcquiredOthers = AcquiredItem::where('purchase_item_id', $purchaseItem->id)
                                ->where('id', '!=', $acquiredItem->id)
                                ->sum('quantity');

                            // Check if new quantity exceeds available balance
                            $availableBalance = $purchaseItem->quantity - $totalAcquiredOthers;

                            if ($quantity > $availableBalance) {
                                return redirect()->back()
                                    ->with('error', "Quantity for {$purchaseItem->item_description} exceeds available balance of {$availableBalance}")
                                    ->withInput();
                            }

                            // Update store quantities
                            $storeItem = Store::where('item_name', $acquiredItem->item_description)->first();
                            if ($storeItem) {
                                // Adjust store quantity: remove old quantity, add new quantity
                                $newStoreQuantity = $storeItem->quantity - $acquiredItem->quantity + $quantity;
                                $storeItem->update(['quantity' => $newStoreQuantity]);
                            }

                            // Update acquired item quantity
                            $acquiredItem->update(['quantity' => $quantity]);
                        }
                    }
                }
            });

            return redirect()->route('acquired.index')
                ->with('success', 'Acquired materials updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not update due to internal server error. please consult technical support.')->withInput(['items' => $request->items]);
        }
    }

    public function destroy($id)
    {
        //get the record by id
        $record = Acquired::with('items')->find($id);

        if (!$record) {
            return redirect()
                ->back()
                ->with('error', 'Acquired record not found');
        }

        //deleting when its items are empty
        if ($record->items->isEmpty()) {
            $record->delete();
            return redirect()
                ->back()
                ->with('success', 'Acquired purchase requisition deleted successfully');
        }

        //deleting when its items are not empty
        foreach ($record->items as $item) {
            //get the item in stores
            $storeItem = Store::where('item_name', $item->item_description)->first();

            //update its quantity only if store item exists
            if ($storeItem) {
                $newQuantity = $storeItem->quantity - $item->quantity;

                // Prevent negative quantities
                if ($newQuantity < 0) {
                    return redirect()
                        ->back()
                        ->with('error', "Cannot delete acquisition. Store only has {$storeItem->quantity} {$item->item_description} but trying to remove {$item->quantity}. Some items may have already been issued from store.");
                }

                $storeItem->update(['quantity' => $newQuantity]);
            }
        }

        $record->delete();

        return redirect()
            ->back()
            ->with('success', 'Acquired purchase requisition deleted successfully');
    }


    public function search()
    {
        //find items matching the query
        $items = Acquired::where("purchase_requisition_id", "LIKE", "%" . request('q') . "%")->get();

        //if results are empty redirect back with no results found
        if ($items->isEmpty()) {
            return redirect()->route('acquired.index')->with('error', 'No records found')->withInput();
        } else {

            //return the results
            return view('acquired.search', [
                'items' => $items,
                'query' => request('q'),
            ]);
        }
    }

    public function create_item($acquired_id)
    {
        //return id and view to a form of creating new acquired item
        return view('acquired.items.create', [
            'acquired_id' => $acquired_id
        ]);
    }

    public function store_item(Request $request)
    {

        //validate
        $validated = $request->validate([
            'item_description' => ['required', 'string', 'max:50'],
            'quantity' => ['required', 'integer']
        ]);

        //get the purchase requisition id
        $purchase = Acquired::where('id', $request->requisition_id)->first();

        //check if that item was ordered
        $ordered_item = PurchaseItem::where('purchase_requisition_id', $purchase->purchase_requisition_id)->
            where('item_description', $validated['item_description'])->first();

        if (!$ordered_item) {
            return redirect()->back()->with('error', $validated['item_description'] . ' was not ordered in this purchase requisition')
                ->withInput();
        }

        //check if the item already exists in the acquired items
        $existing_item = AcquiredItem::where('item_description', $validated['item_description'])->
            where('acquired_id', $request->requisition_id)->first();

        if ($existing_item) {
            return redirect()->back()->with('error', $validated['item_description'] . ' already acquired')
                ->withInput();
        }

        //find the balance
        $ordered_quantity = $ordered_item->quantity;
        $balance = $ordered_quantity - $validated['quantity'];

        //validate the balance
        if ($balance < 0) {
            return redirect()->back()->with('error', 'The quantity provided is more than what was ordered, ordered quantity for ' . $validated['item_description'] . ' is ' . $ordered_quantity)
                ->withInput();
        } else {


            //store the item
            AcquiredItem::create([
                'item_description' => $validated['item_description'],
                'acquired_id' => $request->requisition_id,
                'quantity' => $validated['quantity'],
                'balance' => $balance
            ]);
            // Find if the item exists in the Store
            $existingItem = Store::where('item_name', $validated['item_description'])->first();

            if (!$existingItem) {
                // Item does not exist, create a new record
                Store::create([
                    'item_name' => $validated['item_description'],
                    'quantity' => $validated['quantity']
                ]);
            } else {
                // Item exists, update its quantity
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $request->quantity
                ]);
            }
            //redirect to item.index with success message
            return redirect()->route('item.index', $request->requisition_id)->with('success', 'item created successfully.');


        }
    }

    //show all items for an acquired purchase requisition
    public function index_item($id)
    {

        //get all items matching the requisition id
        $items = AcquiredItem::with('acquired')->where('acquired_id', $id)->get();

        //return them to item.index view
        return view('acquired.items.index', [
            'items' => $items,
            'id' => $id
        ]);
    }
}
