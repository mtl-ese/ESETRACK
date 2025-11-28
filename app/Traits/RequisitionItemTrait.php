<?php
namespace App\Traits;

use App\Models\Store;
use App\Models\StoreItem;
use App\Models\ItemSerialNumber;
use App\Models\StoreRequisitionSerialNumber;
use App\Models\StoreRequisitionDestinationItem;

trait RequisitionItemTrait
{
    /**
     * Create or update a requisition item for a specific destination.
     */
    public function createRequisitionItem(array $itemData, string $requisitionId)
    {
        $itemName = $itemData['item_name'];
        $quantity = (int) ($itemData['quantity'] ?? 0);
        $serialNumbers = $itemData['serialNumbers'] ?? [];
        $destinationLinkId = $itemData['destination_link_id'];

        if ($quantity <= 0) {
            throw new \Exception("Quantity must be greater than 0 for {$itemName}");
        }

        // Ensure stock exists for this item (if not, create it)
        $storeItemStock = Store::firstOrCreate(
            ['item_name' => $itemName],
            ['quantity' => 0]
        );

        // Get or create the main requisition item (StoreItem)
        $requisitionItem = StoreItem::firstOrCreate(
            ['store_requisition_id' => $requisitionId, 'item_name' => $itemName],
            ['quantity' => 0]
        );

        // --- IMPORTANT: Get previous destination quantity BEFORE updating ---
        $previousDestinationQty = StoreRequisitionDestinationItem::where('store_item_id', $requisitionItem->id)
            ->where('destination_link_id', $destinationLinkId)
            ->value('quantity') ?? 0;

        // Save/update destination item with quantity & serials
        $this->saveRequisitionItemSerials(
            $requisitionItem->id,
            $itemName,
            $quantity,
            $serialNumbers,
            $destinationLinkId,
            $requisitionId
        );

        // Recalculate total quantity across all destinations
        $totalQuantity = StoreRequisitionDestinationItem::where('store_item_id', $requisitionItem->id)
            ->sum('quantity');

        $requisitionItem->update(['quantity' => $totalQuantity]);

        // ----------------------------------------------------------
        // STOCK ADJUSTMENTS: HANDLE INCREASE OR DECREASE
        // ----------------------------------------------------------

        if ($quantity > $previousDestinationQty) {

            // User increased quantity → need extra stock
            $requiredExtra = $quantity - $previousDestinationQty;

            if ($storeItemStock->quantity < $requiredExtra) {
                throw new \Exception("Not enough stock for {$itemName}. Available: {$storeItemStock->quantity}");
            }

            // Deduct extra
            $storeItemStock->quantity -= $requiredExtra;

        } elseif ($quantity < $previousDestinationQty) {

            // User decreased quantity → return difference back to stock
            $returned = $previousDestinationQty - $quantity;

            $storeItemStock->quantity += $returned;

        }

        // Save stock changes
        $storeItemStock->save();

        return $requisitionItem;
    }

    /**
     * Save or update serial numbers & destination items for a requisition item.
     */
    public function saveRequisitionItemSerials(
        int $requisitionItemId,
        string $itemName,
        int $quantity,
        array $serialNumbers,
        int $destinationLinkId,
        string $requisitionId
    ) {
        // Check if destination item exists
        $destinationItem = StoreRequisitionDestinationItem::firstOrNew([
            'store_item_id' => $requisitionItemId,
            'destination_link_id' => $destinationLinkId
        ]);

        $destinationItem->quantity = $quantity;
        $destinationItem->serials = $serialNumbers;
        $destinationItem->save();

        // Save serials
        foreach ($serialNumbers as $serial) {
            $serialRecord = ItemSerialNumber::firstOrCreate(
                ['serial_number' => $serial],
                ['item_name' => $itemName]
            );

            StoreRequisitionSerialNumber::firstOrCreate([
                'store_requisition_id' => $requisitionId,
                'item_serial_number_id' => $serialRecord->id
            ]);
        }
    }
}
