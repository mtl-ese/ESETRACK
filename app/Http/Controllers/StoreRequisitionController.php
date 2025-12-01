<?php

namespace App\Http\Controllers;

use App\Models\RecoveryStoreRequisition;
use App\Models\Store;
use App\Models\StoreItem;
use App\Models\StoreRequisition;
use App\Traits\RequisitionItemTrait;
use App\Models\StoreRequisitionDestination;
use App\Models\StoreRequisitionDestinationLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreRequisitionController extends Controller
{
    use RequisitionItemTrait;

    /**
     * Display all store requisitions.
     */
    public function index()
    {
        $stores = StoreRequisition::latest()->with('destinationLinks.destination', 'creator')->get();
        return view('storeReq.index', ['stores' => $stores]);
    }

    /**
     * Show form for creating a new requisition.
     */
    public function create()
    {
        return view('storeReq.create');
    }

    /**
     * Store a newly created store requisition.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'requisition_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^MTL\s\d{1,10}$/', $value)) {
                        $fail('The ' . $attribute . ' must start with "MTL" followed by a space and a number.');
                    }
                }
            ],
            'approved_by' => ['required', 'string'],
            'requisition_date' => ['required', 'date', 'before_or_equal:today'],
            'destinations' => ['required', 'array', 'min:1'],
            'destinations.*.client' => ['required', 'string'],
            'destinations.*.location' => ['required', 'string'],
        ]);

        // Check if requisition ID already exists
        if (
            StoreRequisition::where('requisition_id', $validated['requisition_id'])->exists() ||
            RecoveryStoreRequisition::where('recovery_requisition_id', $validated['requisition_id'])->exists()
        ) {
            return redirect()->back()->with('error', 'Requisition ID already exists.')->withInput();
        }

        $user = Auth::user();

        // Create the requisition
        $requisition = StoreRequisition::create([
            'requisition_id' => $validated['requisition_id'],
            'approved_by' => $validated['approved_by'],
            'requested_on' => $validated['requisition_date'],
            'created_by' => $user->id
        ]);

        // Create destination links
        foreach ($validated['destinations'] as $dest) {
            $destination = StoreRequisitionDestination::firstOrCreate([
                'client' => $dest['client'],
                'location' => $dest['location']
            ]);

            StoreRequisitionDestinationLink::firstOrCreate([
                'store_requisition_id' => $requisition->requisition_id,
                'destination_id' => $destination->id
            ]);
        }

        return redirect()
            ->route('store.add-items-form', ['requisition_id' => $validated['requisition_id']])
            ->with('success', 'You can now add materials to the store requisition.');
    }

    /**
     * Show a store requisition and its items.
     */
    public function show($requisition_id)
    {
        $requisition = StoreRequisition::where('requisition_id', $requisition_id)->firstOrFail();

        $items = StoreItem::with(['destinationItems.link.destination', 'serial_numbers'])
            ->where('store_requisition_id', $requisition_id)
            ->get();

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

        return view('storeReq.items.index', [
            'items' => $items,
            'requisition' => $requisition,
            'destinations' => $destinations
        ]);
    }

    /**
     * Delete a store requisition safely.
     */
    public function destroy($requisition_id)
    {
        $record = StoreRequisition::with(['items', 'recoveries'])
            ->where('requisition_id', $requisition_id)->first();

        if (!$record) {
            return redirect()->back()->with('error', 'Requisition not found.');
        }

        if ($record->recoveries->isNotEmpty()) {
            return redirect()->back()->with('error', 'Cannot delete a requisition that has been recovered.');
        }

        // Return quantities to stock
        foreach ($record->items as $item) {
            $storeItem = Store::firstOrCreate(['item_name' => $item->item_name], ['quantity' => 0]);
            $storeItem->quantity += $item->quantity;
            $storeItem->save();
        }

        $record->delete();

        return redirect()->back()->with('success', 'Store Requisition deleted successfully.');
    }

    /**
     * Search store requisitions.
     */
    public function search()
    {
        $query = request('q');

        $stores = StoreRequisition::with('creator')
            ->where('requisition_id', 'LIKE', "%{$query}%")
            ->orWhere('client_name', 'LIKE', "%{$query}%")
            ->get();

        if ($stores->isEmpty()) {
            return redirect()->route('store.index')->with('error', 'No records found')->withInput();
        }

        return view('storeReq.search', ['stores' => $stores, 'query' => $query]);
    }

    /**
     * Show the edit form for a store requisition.
     */
    public function editForm($requisition_id)
    {
        $requisition = StoreRequisition::where('requisition_id', $requisition_id)->firstOrFail();

        // Load items with serial numbers and destination relationships
        $items = StoreItem::with([
            'serial_numbers' => function ($query) use ($requisition_id) {
                $query->whereHas('storeRequisitionHistory', function ($q) use ($requisition_id) {
                    $q->where('store_requisition_id', $requisition_id);
                });
            },
            'destinationItems.link.destination'
        ])
            ->where('store_requisition_id', $requisition_id)
            ->get();

        // Load all destination links for this requisition (even if no items yet)
        $allLinks = StoreRequisitionDestinationLink::with('destination')
            ->where('store_requisition_id', $requisition_id)
            ->get();

        // Build unique destinations from items + all links
        $destinationsFromItems = $items->flatMap(function ($item) {
            $destinations = [];

            if ($item->destination_link_id) {
                $link = $item->destinationItems->firstWhere('destination_link_id', $item->destination_link_id)?->link;
                if ($link) {
                    $destinations[] = [
                        'id' => (int) $item->destination_link_id,
                        'client' => $link->destination?->client ?? '',
                        'location' => $link->destination?->location ?? '',
                        'display' => ($link->destination?->client ?? '') . ' - ' . ($link->destination?->location ?? ''),
                    ];
                }
            }

            foreach ($item->destinationItems as $di) {
                $link = $di->link;
                if ($link) {
                    $destinations[] = [
                        'id' => (int) $link->id,
                        'client' => $link->destination?->client ?? '',
                        'location' => $link->destination?->location ?? '',
                        'display' => ($link->destination?->client ?? '') . ' - ' . ($link->destination?->location ?? ''),
                    ];
                }
            }

            return $destinations;
        });

        $destinationsFromLinks = $allLinks->map(function ($link) {
            return [
                'id' => (int) $link->id,
                'client' => $link->destination?->client ?? '',
                'location' => $link->destination?->location ?? '',
                'display' => ($link->destination?->client ?? '') . ' - ' . ($link->destination?->location ?? ''),
            ];
        });

        // Merge and remove duplicates
        $destinations = $destinationsFromItems
            ->merge($destinationsFromLinks)
            ->unique('id')
            ->values();

        // Prepare items data for JS
        $itemsData = $items->map(function ($item) {
            $serialNumbers = $item->serial_numbers
                ->pluck('serial_number')
                ->filter()
                ->values()
                ->all();

            return [
                'item_name' => $item->item_name,
                'quantity' => $item->quantity,
                'serialNumbers' => $serialNumbers,
                'destination_link_id' => $item->destination_link_id !== null ? (int) $item->destination_link_id : null,
            ];
        });

        // Destinations lookup for JS
        $destinationsLookup = $destinations->mapWithKeys(function ($d) {
            return [$d['id'] => $d];
        });

        return view('storeReq.edit', compact(
            'requisition',
            'items',
            'destinations',
            'requisition_id',
            'itemsData',
            'destinationsLookup'
        ));
    }


    /**
     * Update a store requisition and its items using trait logic.
     *//*
public function updateAll(Request $request, $requisition_id)
{
$validated = $request->validate([
'approved_by' => ['required', 'string'],
'requisition_date' => ['required', 'date', 'before_or_equal:today'],
'item_diversion_note' => ['nullable', 'string'],
'items' => ['nullable', 'json'],
'existing_destinations' => ['nullable', 'array'],
'new_destinations' => ['nullable', 'array'],
'new_destinations.*.client' => ['required_with:new_destinations', 'string'],
'new_destinations.*.location' => ['required_with:new_destinations', 'string'],
]);

try {
// dd($request->all());

DB::transaction(function () use ($validated, $request, $requisition_id) {

$requisition = StoreRequisition::where('requisition_id', $requisition_id)->firstOrFail();

// Update requisition fields
$requisition->update([
'approved_by' => $validated['approved_by'],
'item_diversion_note' => $validated['item_diversion_note'] ?? null,
'requested_on' => $validated['requisition_date'],
]);

// -------------------------------------
// Load existing destination links
// -------------------------------------
$existingLinks = StoreRequisitionDestinationLink::where('store_requisition_id', $requisition_id)
->with('destination')
->get();

$validLinkIds = $existingLinks->pluck('id')->toArray();


// Add new destinations
if (!empty($validated['new_destinations'])) {
foreach ($validated['new_destinations'] as $dest) {
    $destination = StoreRequisitionDestination::firstOrCreate([
        'client' => $dest['client'],
        'location' => $dest['location']
    ]);

    $link = StoreRequisitionDestinationLink::firstOrCreate([
        'store_requisition_id' => $requisition_id,
        'destination_id' => $destination->id
    ]);

    $validLinkIds[] = $link->id;
    $existingLinks->push($link);
}
}

if ($request->deleted_items) {
$deletedIds = json_decode($request->deleted_items, true);

StoreItem::whereIn('id', $deletedIds)->delete();
}


// -------------------------------------
// Handle items
// -------------------------------------
if ($request->items) {
$items = json_decode($request->items, true);

foreach ($items as $item) {
    $itemName = $item['item_name'];
    $quantity = (int) ($item['quantity'] ?? 0);
    $serialNumbers = $item['serialNumbers'] ?? [];
    $destinationLinkId = $item['destination_link_id'] ?? null;

    /* if (empty($destinationLinkId) || !in_array($destinationLinkId, $validLinkIds, true)) {
         throw new \Exception("Invalid destination link {$destinationLinkId} for item {$itemName}");
     }
    if ($destinationLinkId === null) {
        throw new \Exception("Invalid destination link {$destinationLinkId} for item {$itemName}");
    }

    // Allow new_X IDs
    if (!str_starts_with($destinationLinkId, 'new_') && !in_array($destinationLinkId, $validLinkIds, true)) {
        throw new \Exception("Invalid destination link {$destinationLinkId} for item {$itemName}");
    }


    if ($quantity <= 0) {
        throw new \Exception("Quantity must be greater than 0 for {$itemName}");
    }

    // Use trait to handle both new and existing items safely
    $this->createRequisitionItem([
        'item_name' => $itemName,
        'quantity' => $quantity,
        'serialNumbers' => $serialNumbers,
        'destination_link_id' => $destinationLinkId,
    ], $requisition_id);
}
}

// Delete removed destination links
if (!empty($validated['existing_destinations'])) {
StoreRequisitionDestinationLink::where('store_requisition_id', $requisition_id)
    ->whereNotIn('id', $validated['existing_destinations'])
    ->delete();
}
});

return redirect()->route('store.index')->with('success', 'Store requisition updated successfully.');
} catch (\Exception $e) {
return redirect()->back()->with('error', $e->getMessage())->withInput();
}
}*/
    public function updateAll(Request $request, $requisition_id)
    {
        // ----------------- Validation -----------------
        $validated = $request->validate([
            'approved_by' => ['required', 'string'],
            'requisition_date' => ['required', 'date', 'before_or_equal:today'],
            'item_diversion_note' => ['nullable', 'string'],
            'items' => ['nullable', 'json'],
            'existing_destinations' => ['nullable', 'array'],
            'new_destinations' => ['nullable', 'array'],
            'new_destinations.*.client' => ['required_with:new_destinations', 'string'],
            'new_destinations.*.location' => ['required_with:new_destinations', 'string'],
        ]);

        // ------------------ No-change detection ------------------
        $hasChanges = false;

        $requisition = StoreRequisition::where('requisition_id', $requisition_id)->firstOrFail();

        $existingDate = $requisition->requested_on ? date('Y-m-d', strtotime($requisition->requested_on)) : null;
        if (
            $requisition->approved_by !== $validated['approved_by'] ||
            ($validated['item_diversion_note'] ?? null) !== ($requisition->item_diversion_note ?? null) ||
            $existingDate !== $validated['requisition_date']
        ) {
            $hasChanges = true;
        }

        // If deleted items or new destinations provided, it's a change
        if ($request->deleted_items || !empty($validated['new_destinations'])) {
            $hasChanges = true;
        }

        // Compare items if provided - normalize and compare with DB
        if (!$hasChanges && $request->items) {
            $submittedItems = json_decode($request->items, true) ?: [];

            // Build existing destination item entries from DB
            $existingEntries = [];
            $storeItems = StoreItem::where('store_requisition_id', $requisition_id)->with('destinationItems')->get();
            foreach ($storeItems as $si) {
                foreach ($si->destinationItems as $di) {
                    $serials = $di->serials;
                    if (is_string($serials)) {
                        $decoded = json_decode($serials, true);
                        $serials = $decoded === null ? [] : $decoded;
                    }
                    $existingEntries[] = [
                        'destination_link_id' => (int) $di->destination_link_id,
                        'quantity' => (int) $di->quantity,
                        'serials' => array_values(array_map('strval', (array) $serials)),
                    ];
                }
            }

            $submittedEntries = [];
            foreach ($submittedItems as $it) {
                $submittedEntries[] = [
                    'destination_link_id' => is_numeric($it['destination_link_id'] ?? null) ? (int) $it['destination_link_id'] : ($it['destination_link_id'] ?? null),
                    'quantity' => (int) ($it['quantity'] ?? 0),
                    'serials' => array_values(array_map('strval', (array) ($it['serialNumbers'] ?? []))),
                ];
            }

            // Normalize by sorting serial lists and sort entries for stable comparison
            $normalize = function ($arr) {
                foreach ($arr as &$e) {
                    sort($e['serials']);
                }
                usort($arr, fn($a, $b) => ($a['destination_link_id'] <=> $b['destination_link_id']));
                return $arr;
            };

            $normExisting = $normalize($existingEntries);
            $normSubmitted = $normalize($submittedEntries);

            if (json_encode($normExisting) !== json_encode($normSubmitted)) {
                $hasChanges = true;
            }
        }

        if (!$hasChanges) {
            return redirect()->back()->with('error', 'Nothing was changed. Please adjust at least one return quantity or serial selection.')->withInput();
        }

        try {
            DB::transaction(function () use ($validated, $request, $requisition_id) {

                $requisition = StoreRequisition::where('requisition_id', $requisition_id)->firstOrFail();

                // ------------- Update requisition fields -------------
                $requisition->update([
                    'approved_by' => $validated['approved_by'],
                    'item_diversion_note' => $validated['item_diversion_note'] ?? null,
                    'requested_on' => $validated['requisition_date'],
                ]);

                // ------------- Load existing destination links -------------
                $existingLinks = StoreRequisitionDestinationLink::where('store_requisition_id', $requisition_id)
                    ->with('destination')
                    ->get();

                $validLinkIds = $existingLinks->pluck('id')->toArray();

                // ------------- Handle new destinations -------------
                $newDestinationMap = []; // tempId => actual link ID
                if (!empty($validated['new_destinations'])) {
                    foreach ($validated['new_destinations'] as $index => $dest) {
                        $destination = StoreRequisitionDestination::firstOrCreate([
                            'client' => $dest['client'],
                            'location' => $dest['location'],
                        ]);

                        $link = StoreRequisitionDestinationLink::firstOrCreate([
                            'store_requisition_id' => $requisition_id,
                            'destination_id' => $destination->id,
                        ]);

                        $validLinkIds[] = $link->id;
                        $existingLinks->push($link);

                        // Map temporary ID (from frontend) to actual DB ID
                        $newDestinationMap["new_$index"] = $link->id;
                    }
                }

                // ------------- Delete removed items -------------
                if ($request->deleted_items) {
                    $deletedIds = json_decode($request->deleted_items, true);
                    StoreItem::whereIn('id', $deletedIds)->delete();
                }

                // ------------- Handle items -------------
                if ($request->items) {
                    $items = json_decode($request->items, true);

                    foreach ($items as $item) {
                        $itemName = $item['item_name'];
                        $quantity = (int) ($item['quantity'] ?? 0);
                        $serialNumbers = $item['serialNumbers'] ?? [];
                        $destinationLinkId = $item['destination_link_id'] ?? null;

                        // Map new temporary destination ID to actual DB ID
                        if ($destinationLinkId && is_string($destinationLinkId) && str_starts_with($destinationLinkId, 'new_')) {
                            if (!isset($newDestinationMap[$destinationLinkId])) {
                                throw new \Exception("Invalid new destination for item {$itemName}");
                            }
                            $destinationLinkId = $newDestinationMap[$destinationLinkId];
                        }

                        if (empty($destinationLinkId) || !in_array((int) $destinationLinkId, $validLinkIds, true)) {
                            throw new \Exception("Invalid destination link for item {$itemName}");
                        }

                        // Save or update item using your trait
                        $this->createRequisitionItem([
                            'item_name' => $itemName,
                            'quantity' => $quantity,
                            'serialNumbers' => $serialNumbers,
                            'destination_link_id' => (int) $destinationLinkId,
                        ], $requisition_id);
                    }
                }
            });

            return redirect()->route('store.index')->with('success', 'Store requisition updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
}