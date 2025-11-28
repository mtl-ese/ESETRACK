<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\StoreItem;
use App\Traits\RequisitionItemTrait;
use App\Models\StoreRequisitionDestinationLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreItemController extends Controller
{
    use RequisitionItemTrait;

    /**
     * Display a listing of all store items.
     */
    public function index()
    {
        $storeItems = StoreItem::with('store_requisition', 'destinationItems.link.destination')->latest()->get();
        return view('storeReq.items.index', [
            'storeItems' => $storeItems
        ]);
    }

    public function MaterialsIndex()
    {
        $storeItems = StoreItem::with('store_requisition', 'destinationItems.link.destination')->latest()->get();
        return view('storeReq.materials.index', [
            'storeItems' => $storeItems
        ]);
    }

    /**
     * Show the form for creating a new item for a requisition.
     */
    public function create($requisition_id)
    {
        $stores = Store::select('id', 'item_name')->distinct()->orderBy('item_name')->get();

        $requisition = \App\Models\StoreRequisition::where('requisition_id', $requisition_id)->first();
        $destinations = [];

        if ($requisition) {
            $destinations = $requisition->destinationLinks()
                ->with('destination')
                ->get()
                ->map(function ($link) {
                    return [
                        'id' => $link->id,
                        'client' => $link->destination->client,
                        'location' => $link->destination->location,
                        'display' => $link->destination->client . ' - ' . $link->destination->location
                    ];
                });
        }

        return view('storeReq.items.create', compact('requisition_id', 'stores', 'destinations'));
    }

    /**
     * Store newly created items for a requisition.
     */
    public function store(Request $request)
    {
        $items = json_decode($request->items, true);

        if (!$items || !is_array($items)) {
            return redirect()->back()->with('error', 'No items to process');
        }

        try {
            DB::transaction(function () use ($items, $request) {
                foreach ($items as $itemData) {
                    if (empty($itemData['destination_link_id'])) {
                        throw new \Exception('Each item must be attached to at least one destination.');
                    }

                    if (
                        !StoreRequisitionDestinationLink::where('id', $itemData['destination_link_id'])
                            ->where('store_requisition_id', $request->requisition_id)
                            ->exists()
                    ) {
                        throw new \Exception("Invalid destination link ID for item {$itemData['item_name']}.");
                    }

                    $this->createRequisitionItem($itemData, $request->requisition_id);
                }
            });

            return redirect()->route('store.show', $request->requisition_id)
                ->with('success', 'All items added successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing an item.
     */
    public function edit($id)
    {
        $item = StoreItem::findOrFail($id);
        $stores = Store::select('id', 'item_name')->distinct()->orderBy('item_name')->get();

        return view('storeReq.items.edit', compact('item', 'stores'));
    }

    /**
     * Update an item in a requisition.
     */
    public function update(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:store_items,id',
            'item_name' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'serialNumbers' => 'nullable|array',
            'serialNumbers.*' => 'string',
            'destination_link_id' => 'required|exists:store_requisition_destination_links,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $requisitionItem = StoreItem::findOrFail($request->item_id);

                // Validate destination link
                $linkOk = StoreRequisitionDestinationLink::where('id', $request->destination_link_id)
                    ->where('store_requisition_id', $requisitionItem->store_requisition_id)
                    ->exists();

                if (!$linkOk) {
                    throw new \Exception("Invalid destination link for updated item {$request->item_name}");
                }

                $itemData = [
                    'item_name' => $request->item_name,
                    'quantity' => $request->quantity,
                    'serialNumbers' => $request->serialNumbers ?? [],
                    'destination_link_id' => $request->destination_link_id,
                ];

                // Use trait to handle update with stock logic
                $this->createRequisitionItem($itemData, $requisitionItem->store_requisition_id);
            });

            return redirect()->back()->with('success', 'Item updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Show serial numbers for a requisition item.
     */
    public function show($requisition_id, $id)
    {
        $item = StoreItem::where('store_requisition_id', $requisition_id)
            ->where('id', $id)
            ->firstOrFail();

        $item_name = $item->item_name;

        $serial_numbers = \App\Models\ItemSerialNumber::whereHas('storeRequisitionHistory', function ($query) use ($requisition_id) {
            $query->where('store_requisition_id', $requisition_id);
        })->where('item_name', $item_name)->get();

        $formatted_serials = $serial_numbers->map(function ($serial) {
            return (object) ['serial_number' => $serial->serial_number];
        });

        return view('storeReq.items.serials.index', [
            'serial_numbers' => $formatted_serials,
            'item_name' => $item_name,
            'requisition_id' => $requisition_id
        ]);
    }
}
