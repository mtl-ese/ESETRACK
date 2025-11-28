<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\RecoveryStore;
use App\Models\RecoveryStoreSerialNumber;
use Illuminate\Http\Request;

class RecoveryStoreController extends Controller
{
    public function index()
    {
        $items = RecoveryStore::with('serial_numbers')->get();

        return view('recovered.index', [
            'items' => $items
        ]);
    }

    public function search()
    {
        $results = RecoveryStore::where('item_name', 'LIKE', '%' . request('q') . '%')->get();
        if ($results->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'No items found');
        } else {
            return view('recovered.search', [
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

                    // If material exists in recovery store, update its quantity; otherwise create it
                    $recoveryStoreItem = RecoveryStore::firstOrCreate(
                        ['item_name' => $itemName],
                        ['quantity' => 0] // if created, quantity set below
                    );

                    // increment quantity (works for existing or newly created record)
                    $recoveryStoreItem->increment('quantity', $quantity);

                    // Handle serial numbers: create only if not already present
                    if (!empty($serials) && is_array($serials)) {
                        foreach ($serials as $s) {
                            $s = trim($s);
                            if ($s === '') {
                                continue;
                            }
                            $exists = RecoveryStoreSerialNumber::where('serial_numbers', $s)->exists();
                            if (!$exists) {
                                RecoveryStoreSerialNumber::create([
                                    'recovery_store_id' => $recoveryStoreItem->id,
                                    'serial_numbers' => $s
                                ]);
                            }
                        }
                    }

                    // ensure model is saved (increment already persisted)
                    $recoveryStoreItem->refresh();
                }
            });

            return redirect()->back()->with('success', 'Recovery store updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not create due to internal server error. please consult technical support.')->withInput();
        }
    }
}

