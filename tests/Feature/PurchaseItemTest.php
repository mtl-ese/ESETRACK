<?php
// tests/Feature/PurchaseItemTest.php
namespace Tests\Feature;

use App\Models\PurchaseItem;
use App\Models\PurchaseRequisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseItemTest extends TestCase
{
    use RefreshDatabase;  // Ensures the database is reset after each test

    /** @test */
    public function it_can_create_a_purchase_item()
    {
        $user = User::factory()->create();
        // Create a PurchaseRequisition for the foreign key relation
        $requisition = PurchaseRequisition::factory()->create(['created_by' => $user->id]);

        // Create a PurchaseItem
        $purchaseItem = PurchaseItem::create([
            'purchase_requisition_id' => $requisition->requisition_id,
            'item_description' => 'Test Item',
            'quantity' => 10,
        ]);

        // Assert the PurchaseItem is created and its properties are correct
        $this->assertDatabaseHas('purchase_items', [
            'purchase_requisition_id' => $requisition->requisition_id,
            'item_description' => 'Test Item',
            'quantity' => 10,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_purchase_requisition()
    {
        $user = User::factory()->create();
        $requisition = PurchaseRequisition::factory()->create(['created_by' => $user->id]);
        $purchaseItem = PurchaseItem::factory()->create([
            'purchase_requisition_id' => $requisition->requisition_id,
        ]);

        // Assert that the PurchaseItem belongs to the correct PurchaseRequisition
        $this->assertEquals($requisition->requisition_id, $purchaseItem->purchase_requisition_id);
    }
}
