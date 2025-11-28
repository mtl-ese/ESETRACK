<?php

namespace App\Http\Controllers;

use App\Models\RecoveryStoreRequisitionItem;
use App\Models\StoreRequisitionSerialNumber;
use App\Models\StoreReturn;
use App\Models\StoreReturnItem;
use App\Models\StoreReturnItemSerialNumber;
use App\Models\StoreItem;
use Illuminate\Http\Request;

class StoreReturnItemController extends Controller
{
    /**
     * Display all items for a specific store return.
     * Includes balance calculation.
     */
    public function index($store_return_id, $requisition_id)
    {
        // Eager-load nested serial links and their actual serial values
        $storeReturn = StoreReturn::with('items.serial_numbers.itemSerialNumber', 'items.destinationLink.destination')->findOrFail($store_return_id);

        // Get totals from original recovery requisition
        $recoveryTotals = RecoveryStoreRequisitionItem::where('recovery_requisition_id', $storeReturn->recovery_requisition_id)
            ->selectRaw('item_name, SUM(quantity) as total')
            ->groupBy('item_name')
            ->pluck('total', 'item_name');

        // Sum of already returned items
        $storeReturnIds = StoreReturn::where('recovery_requisition_id', $storeReturn->recovery_requisition_id)->pluck('id');
        $returnSums = StoreReturnItem::whereIn('store_return_id', $storeReturnIds)
            ->selectRaw('item_name, SUM(quantity) as total')
            ->groupBy('item_name')
            ->pluck('total', 'item_name');

        // Attach balance and flattened serial numbers
        $items = $storeReturn->items->map(function ($item) use ($recoveryTotals, $returnSums) {
            $original = $recoveryTotals[$item->item_name] ?? 0;
            $returned = $returnSums[$item->item_name] ?? 0;
            $item->balance = max($original - $returned, 0);

            // Flatten serial numbers into array of plain strings for display
            $item->serial_numbers_flat = $item->serial_numbers
                ->map(function ($link) {
                    return optional($link->itemSerialNumber)->serial_number;
                })
                ->filter()
                ->values()
                ->all();

            return $item;
        });

        return view('returns.items.index', [
            'items' => $items,
            'store_return_id' => $store_return_id,
            'requisition_id' => $requisition_id,
        ]);
    }

    /**
     * Simplified materials index.
     * Only shows material names and quantities.
     */
    public function materialsIndex()
    {
        $storeReturnItems = StoreReturnItem::with(
            'store_return.recovery_store_requisition',
            'destinationLink.destination'
        )
            ->latest()
            ->get();

        return view('returns.materials.index', [
            'storeReturnItems' => $storeReturnItems,
        ]);
    }

    /**
     * Show form for creating a store return item.
     */
    public function create($store_return_id, $requisition_id)
    {
        return view('returns.items.create', [
            'store_return_id' => $store_return_id,
            'requisition_id' => $requisition_id,
        ]);
    }

    /**
     * Store a new store return item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_return_id' => 'required|exists:store_returns,id',
            'requisition_id' => 'required|exists:store_items,store_requisition_id',
            'item_name' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        // Check for duplicates
        $existingItem = StoreReturnItem::where('store_return_id', $validated['store_return_id'])
            ->where('item_name', $validated['item_name'])
            ->first();

        if ($existingItem) {
            return redirect()->back()->with('error', $validated['item_name'] . ' is already in this store return.');
        }

        // Validate quantity against original store requisition
        $storeItem = StoreItem::where('store_requisition_id', $validated['requisition_id'])
            ->where('item_name', $validated['item_name'])
            ->first();

        if (!$storeItem) {
            return redirect()->back()->with('error', $validated['item_name'] . ' not found in original requisition.');
        }

        if ($validated['quantity'] > $storeItem->quantity) {
            return redirect()->back()->with('error', 'Entered quantity exceeds original requisition quantity.');
        }

        // Create item
        $storeReturnItem = StoreReturnItem::create([
            'store_return_id' => $validated['store_return_id'],
            'item_name' => $validated['item_name'],
            'quantity' => $validated['quantity'],
            'balance' => $storeItem->quantity - $validated['quantity'],
        ]);

        // Handle serial numbers if provided
        if ($request->filled('serialNumbers')) {
            $request->validate([
                'serialNumbers' => 'array',
                'serialNumbers.*' => 'required|string|distinct|min:2|max:50',
            ]);

            foreach ($request->serialNumbers as $serial) {
                $serialRecord = StoreRequisitionSerialNumber::where('serial_number', $serial)
                    ->where('store_requisition_id', $validated['requisition_id'])
                    ->first();

                if ($serialRecord) {
                    StoreReturnItemSerialNumber::create([
                        'store_return_item_id' => $storeReturnItem->id,
                        'item_serial_number_id' => $serialRecord->id,
                    ]);
                }
            }
        }

        return redirect()->route('returns.items.index', [
            'store_return_id' => $validated['store_return_id'],
            'requisition_id' => $validated['requisition_id'],
        ])->with('success', 'Item added successfully.');
    }
}
