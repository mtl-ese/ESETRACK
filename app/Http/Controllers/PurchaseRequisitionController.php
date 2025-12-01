<?php

namespace App\Http\Controllers;

use App\Models\AcquiredItem;
use App\Models\PurchaseItem;
use App\Models\PurchaseRequisition;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Database\Eloquent\Builder;

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
            'requisition_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^MTL\s\d{1,10}$/', $value)) {
                        $fail('The ' . $attribute . ' must start with "MTL" followed by a space and a number.');
                    }
                }
            ],
            'project_description' => ['required', 'string'],
            'requisition_date' => ['required', 'date'],
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
            'project_description' => $request->project_description,
            'requested_on' => $request->requisition_date,
            'approved_by' => $request->approved_by,
            'created_by' => $user->id
        ]);

        return redirect()->route('purchase.add-items-form', $request->requisition_id)->with('success', 'You can now add materials to the purchase requisition.');


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


    public function editForm($requisition_id)
    {
        $requisition = PurchaseRequisition::where('requisition_id', $requisition_id)->first();
        $items = PurchaseItem::where('purchase_requisition_id', $requisition_id)->get();

        if (!$requisition) {
            return redirect()->back()->with('error', 'Purchase requisition not found');
        }

        // Check if ANY item has been acquired (even partially)
        $hasAnyAcquiredItems = AcquiredItem::whereHas('acquired', function ($query) use ($requisition_id) {
            $query->where('purchase_requisition_id', $requisition_id);
        })->exists();

        if ($hasAnyAcquiredItems) {
            return redirect()->back()->with('error', 'Cannot edit purchase requisition - some materials have already been acquired');
        }

        return view('purchaseReq.edit', [
            'requisition' => $requisition,
            'items' => $items,
            'requisition_id' => $requisition_id
        ]);
    }

    public function updateAll(Request $request, $requisition_id)
    {
        $requisition = PurchaseRequisition::where('requisition_id', $requisition_id)->first();

        // Check if ANY item has been acquired (even partially)
        $hasAnyAcquiredItems = AcquiredItem::whereHas('acquired', function ($query) use ($requisition_id) {
            $query->where('purchase_requisition_id', $requisition_id);
        })->exists();

        if ($hasAnyAcquiredItems) {
            return redirect()->back()->with('error', 'Cannot edit purchase requisition - some materials have already been acquired');
        }

        $validated = $request->validate([
            'project_description' => ['required', 'string'],
            'approved_by' => ['required', 'string'],
            'requisition_date' => ['required', 'date', 'before_or_equal:today'],
            'items' => ['nullable', 'json']
        ]);

        // Quick no-change detection: compare top-level fields and items payload with existing DB state
        $hasChanges = false;

        // Compare basic fields
        $existingDate = $requisition->requested_on ? date('Y-m-d', strtotime($requisition->requested_on)) : null;
        if (
            $requisition->project_description !== $validated['project_description'] ||
            $requisition->approved_by !== $validated['approved_by'] ||
            $existingDate !== $validated['requisition_date']
        ) {
            $hasChanges = true;
        }

        // Compare items if provided
        $submittedItems = [];
        if ($request->items) {
            $submittedItems = json_decode($request->items, true) ?? [];
        }

        $existingItems = PurchaseItem::where('purchase_requisition_id', $requisition_id)
            ->get(['item_description', 'quantity'])
            ->map(function ($i) {
                return ['item_description' => $i->item_description, 'quantity' => (int) $i->quantity];
            })->values()->all();

        if (!$hasChanges) {
            // Normalize and compare items arrays
            $normSubmitted = collect($submittedItems)->map(function ($it) {
                return ['item_description' => $it['item_description'] ?? '', 'quantity' => (int) ($it['quantity'] ?? 0)];
            })->values()->all();

            // Compare by JSON stable encoding after sorting
            $compare = json_encode(collect($normSubmitted)->sortBy('item_description')->values()->all()) !==
                json_encode(collect($existingItems)->sortBy('item_description')->values()->all());

            if ($compare)
                $hasChanges = true;
        }

        if (!$hasChanges) {
            return redirect()->back()->with('error', 'Nothing was changed. Please adjust at least one return quantity or serial selection.')->withInput();
        }

        // Update requisition details
        $requisition->update([
            'project_description' => $validated['project_description'],
            'approved_by' => $validated['approved_by'],
            'requested_on' => $validated['requisition_date'],
        ]);

        // Update items if provided
        if ($request->items) {
            $items = json_decode($request->items, true);

            // Delete existing items
            PurchaseItem::where('purchase_requisition_id', $requisition_id)->delete();

            // Create new items
            foreach ($items as $item) {
                $storeItem = PurchaseItem::create([
                    'purchase_requisition_id' => $requisition_id,
                    'item_description' => $item['item_description'],
                    'quantity' => $item['quantity'],
                ]);
            }
        }

        return redirect()->route('purchase.index', $requisition_id)
            ->with('success', 'Purchase requisition updated successfully');
    }

    public function destroy($requisition_id)
    {
        $record = PurchaseRequisition::with('acquired.items')->where('requisition_id', $requisition_id)->first();

        // Check if there are any actual acquired items, not just an empty acquired record
        $hasAcquiredItems = $record->acquired->isNotEmpty() && $record->acquired->some(function ($acquired) {
            return $acquired->items->isNotEmpty();
        });

        if (!$hasAcquiredItems) {
            // Delete any empty acquired records first
            $record->acquired()->delete();
            $record->delete();
        } else {
            return redirect()->back()->with('error', 'Sorry, you cannot delete a purchase requisition that has already been acquired');
        }
        return redirect()->route('purchase.index')->with('success', $requisition_id . ' purchase requisition deleted successfully');
    }
}

