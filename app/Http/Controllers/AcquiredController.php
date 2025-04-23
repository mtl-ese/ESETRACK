<?php

namespace App\Http\Controllers;

use App\Models\Acquired;
use App\Models\AcquiredItem;
use App\Models\PurchaseItem;
use App\Models\PurchaseRequisition;
use App\Models\Store;
use Illuminate\Http\Request;

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

    public function create()
    {
        return view('acquired.create');
    }

    //store a new acquired purchase
    public function store(Request $request)
    {
        $validated = $request->validate([
            'requisition_id' => ['required'],
        ]);

        //check if id already exists
        $record = Acquired::where('purchase_requisition_id', $validated['requisition_id'])->get();
        if ($record->isNotEmpty()) {
            return redirect()->back()->with('error', 'Acquired purchase requisition ID already exists.')
                ->withInput();

        }
        //find that requisition in purchase requisition table
        $requisition = PurchaseRequisition::where('requisition_id', $validated['requisition_id'])->get();

        //check if purchase requisition for the given requisition exists
        if ($requisition->isNotEmpty()) {

            //store the record
            Acquired::create([
                'purchase_requisition_id' => $validated['requisition_id'],
            ]);

            //redirect to acquired.index
            return redirect()->route('acquired.index')->with('success', 'Acquired purchase requisition created successfully');

        } else {

            //redirect back with error
            return redirect()->back()->with('error', 'No such purchase requisition ID exists.')
                ->withInput();
        }

    }

    public function destroy($requisition_id)
    {
        //get the record
        $record = Acquired::with('items')->where('purchase_requisition_id', $requisition_id)->first();

        //deleting when its items are empty
        if ($record->items->isEmpty()) {
            $record->delete();
            return redirect()
                ->back()
                ->with('success', 'Acquired purchase requisition deleted succesfully');
        }

        //deleting when its items are not empty
        foreach ($record->items as $item) {

            //get the item in stores
            $storeItem = Store::where('item_name', $item->item_description)->first();

            //update its quantity
            $balance = $storeItem->quantity - $item->quantity;
            $storeItem->update(['quantity' => $balance]);

        }
        $record->delete();

        return redirect()
            ->back()
            ->with('success', 'Acquired purchase requisition deleted succesfully');

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
