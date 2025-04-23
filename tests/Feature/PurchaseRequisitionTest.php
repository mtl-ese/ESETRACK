<?php

namespace Tests\Unit;

use App\Models\PurchaseRequisition;
use App\Models\User;
use App\Models\PurchaseItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseRequisitionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_purchase_requisition()
    {
        $user = User::factory()->create();
        
        $purchaseRequisition = PurchaseRequisition::factory()->create([
            'requisition_id' => 'REQ123',
            'created_by' => $user->id,
            'approved_by' => 'Thokozani'
        ]);

        $this->assertDatabaseHas('purchase_requisitions', [
            'requisition_id' => 'REQ123',
            'created_by' => $user->id
        ]);
    }

    /** @test */
    public function it_belongs_to_a_creator()
    {
        $user = User::factory()->create();
        $purchaseRequisition = PurchaseRequisition::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $purchaseRequisition->creator);
        $this->assertEquals($user->id, $purchaseRequisition->creator->id);
    }

    /** @test */
    public function it_has_many_items()
    {
        $purchaseRequisition = PurchaseRequisition::factory()->create();
        $items = PurchaseItem::factory()->count(3)->create(['purchase_requisition_id' => $purchaseRequisition->requisition_id]);

        $this->assertCount(3, $purchaseRequisition->items);
        $this->assertInstanceOf(PurchaseItem::class, $purchaseRequisition->items->first());
    }
}
