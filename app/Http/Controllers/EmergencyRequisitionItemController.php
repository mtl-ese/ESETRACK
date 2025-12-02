<?php

namespace App\Http\Controllers;

use App\Models\EmergencyRequisition;
use App\Models\EmergencyRequisitionItem;
use App\Models\EmergencyRequisitionItemSerial;
use App\Models\ReturnsStore;
use App\Models\ReturnsStoreSerialNumber;
use Illuminate\Support\Facades\DB;
use App\Models\Store;
use Illuminate\Http\Request;

class EmergencyRequisitionItemController extends Controller
{
    public function index($requisition_id)
    {
        $items = EmergencyRequisitionItem::with('serial_numbers')->where('emergency_requisition_id', $requisition_id)->get();

        return view('emergency.items.index', [
            'items' => $items,
            'requisition_id' => $requisition_id
        ]);
    }

    public function materialsIndex()
    {
        $emergencyItems = EmergencyRequisitionItem::with('requisition')->latest()->get();
        return view('emergency.materials.index', [
            'emergencyItems' => $emergencyItems
        ]);
    }

    public function create($requisition_id)
    {
        $stores = Store::select('id', 'item_name')->distinct()->orderBy('item_name')->get();
        $returnsStores = ReturnsStore::select('id', 'item_name')->distinct()->orderBy('item_name')->get();

        return view('emergency.items.create', [
            'requisition_id' => $requisition_id,
            'stores' => $stores,
            'returnsStores' => $returnsStores
        ]);
    }

    public function store(Request $request, $requisition_id = null)
    {


        // determine requisition id (from route parameter or request payload)
        $requisitionId = $requisition_id ?? $request->requisition_id;

        //check if id exists
        $id = EmergencyRequisition::where('requisition_id', $requisitionId)->first();
        if (!$id) {
            return redirect()
                ->back()
                ->with('error', 'Sorry, Emergency Requisition ID not valid.')
                ->withInput();
        }

        // Accept both single item submissions (legacy) and batched JSON 'items'
        $itemsPayload = null;
        if ($request->filled('items')) {
            $itemsPayload = json_decode($request->items, true);
            if (!is_array($itemsPayload)) {
                return redirect()->back()->with('error', 'Invalid items payload')->withInput();
            }
        } else {
            // convert single submission into an array for unified processing
            $single = [
                'item_name' => $request->input('item_name') ?? $request->input('item_description'),
                'quantity' => $request->input('quantity'),
                'from' => $request->input('from'),
                'serialNumbers' => $request->input('serialNumbers') ?? [],
                'will_return' => $request->input('will_return') ?? null,
            ];
            $itemsPayload = [$single];
        }

        // Process all items transactionally
        DB::beginTransaction();
        try {
            foreach ($itemsPayload as $item) {
                // basic validation per item
                $validator = \Illuminate\Support\Facades\Validator::make($item, [
                    'item_name' => ['required', 'string'],
                    'quantity' => ['required', 'numeric', 'min:1'],
                    'from' => ['required', 'in:stores,return stores'],
                    'serialNumbers' => ['nullable', 'array'],
                    'serialNumbers.*' => ['string']
                ]);

                if ($validator->fails()) {
                    throw new \Exception('Validation failed for one of the items: ' . implode(', ', $validator->errors()->all()));
                }

                $itemName = $item['item_name'];
                $quantity = (int) $item['quantity'];
                $from = $item['from'];
                $serials = $item['serialNumbers'] ?? [];
                $willReturn = isset($item['will_return']) && ($item['will_return'] === 'on' || $item['will_return'] === true || $item['will_return'] === 1) ? 1 : 0;

                // check duplicates against existing items in this requisition
                $existing = EmergencyRequisitionItem::where('item_name', $itemName)
                    ->where('emergency_requisition_id', $requisitionId)
                    ->first();
                if ($existing) {
                    throw new \Exception("Item {$itemName} is already stored in this emergency requisition.");
                }

                if ($from === 'stores') {
                    $storeItem = Store::where('item_name', $itemName)->first();
                    if (!$storeItem) {
                        throw new \Exception("{$itemName} is not available in stores");
                    }

                    $balance = $storeItem->quantity - $quantity;
                    if ($balance < 0) {
                        throw new \Exception("The requested quantity for {$itemName} is more than what is available in stores. Current quantity: {$storeItem->quantity}");
                    }

                    // create emergency item
                    $newItem = EmergencyRequisitionItem::create([
                        'emergency_requisition_id' => $requisitionId,
                        'item_name' => $itemName,
                        'quantity' => $quantity,
                        'from' => $from,
                        'same_to_return' => $willReturn,
                    ]);

                    // store serials if provided
                    foreach ($serials as $s) {
                        EmergencyRequisitionItemSerial::create([
                            'item_id' => $newItem->id,
                            'serial_number' => $s
                        ]);
                    }

                    // deduct from stores
                    $storeItem->update(['quantity' => $balance]);
                } else { // return stores
                    $returnItem = ReturnsStore::where('item_name', $itemName)->first();
                    if (!$returnItem) {
                        throw new \Exception("{$itemName} is not available in return stores");
                    }

                    $balance = $returnItem->quantity - $quantity;
                    if ($balance < 0) {
                        throw new \Exception("The requested quantity for {$itemName} is more than what is available in return stores. Current quantity: {$returnItem->quantity}");
                    }

                    // If serials provided, ensure they exist
                    if (!empty($serials)) {
                        $serial_numbers = ReturnsStoreSerialNumber::where('returns_store_id', $returnItem->id)
                            ->pluck('serial_numbers')
                            ->toArray();

                        if (!empty(array_diff($serials, $serial_numbers))) {
                            throw new \Exception('The serial number(s) do not match those in return stores for ' . $itemName);
                        }
                    }

                    // update return store quantity
                    $returnItem->quantity = $balance;
                    $returnItem->save();

                    // create emergency item
                    $newItem = EmergencyRequisitionItem::create([
                        'emergency_requisition_id' => $requisitionId,
                        'item_name' => $itemName,
                        'quantity' => $quantity,
                        'from' => $from,
                        'same_to_return' => $willReturn,
                    ]);

                    // store serials and remove from return store serials
                    foreach ($serials as $s) {
                        EmergencyRequisitionItemSerial::create([
                            'item_id' => $newItem->id,
                            'serial_number' => $s
                        ]);

                        ReturnsStoreSerialNumber::where('serial_numbers', $s)->delete();
                    }

                    if ($balance == 0) {
                        $returnItem->delete();
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('emergencyItemsIndex', $requisitionId)
                ->with('success', 'All items added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }
}
