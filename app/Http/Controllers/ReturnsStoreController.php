<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\ReturnsStore;
use App\Models\ReturnsStoreSerialNumber;
use Illuminate\Http\Request;

class ReturnsStoreController extends Controller
{
    public function index()
    {
        $items = ReturnsStore::with('serial_numbers')->get();

        return view('return.index', [
            'items' => $items
        ]);
    }

    public function search()
    {
        $results = ReturnsStore::where('item_name', 'LIKE', '%' . request('q') . '%')->get();
        if ($results->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'No items found');
        } else {
            return view('return.search', [
                'items' => $results,
                'query' => request('q')
            ]);
        }
    }

    public function store(Request $request)
    {
        // Expecting a batch payload similar to other controllers: items (json) and requisition_id (optional)
        if (!$request->has('items')) {
            return redirect()->back()->with('error', 'No items provided');
        }

        $items = json_decode($request->items, true);
        if (!$items || !is_array($items)) {
            return redirect()->back()->with('error', 'No items to process');
        }

        try {
            DB::transaction(function () use ($items) {
                foreach ($items as $itemData) {
                    $itemName = trim($itemData['item_name'] ?? '');
                    $quantity = intval($itemData['quantity'] ?? 0);
                    $serials = $itemData['serialNumbers'] ?? [];

                    if ($itemName === '' || $quantity <= 0) {
                        throw new \Exception('Invalid item name or quantity for one of the items');
                    }

                    // If material exists in returns store, update its quantity; otherwise create it
                    $returnsStoreItem = ReturnsStore::firstOrCreate(
                        ['item_name' => $itemName],
                        ['quantity' => 0] // if created, quantity set below
                    );

                    // increment quantity (works for existing or newly created record)
                    $returnsStoreItem->increment('quantity', $quantity);

                    // Handle serial numbers: create only if not already present
                    if (!empty($serials) && is_array($serials)) {
                        foreach ($serials as $s) {
                            $s = trim($s);
                            if ($s === '') {
                                continue;
                            }
                            $exists = ReturnsStoreSerialNumber::where('serial_numbers', $s)->exists();
                            if (!$exists) {
                                ReturnsStoreSerialNumber::create([
                                    'returns_store_id' => $returnsStoreItem->id,
                                    'serial_numbers' => $s
                                ]);
                            }
                        }
                    }

                    // ensure model is saved (increment already persisted)
                    $returnsStoreItem->refresh();
                }
            });

            return redirect()->back()->with('success', 'Returns store updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput(['items' => $request->items]);
        }
    }
}
