<?php

namespace Tests\Unit;

use App\Models\StoreRequisition;
use App\Models\User;
use App\Models\StoreItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreRequisitionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_store_requisition()
    {
        $user = User::factory()->create();
        
        $storeRequisition = StoreRequisition::factory()->create([
            'requisition_id' => 'REQ456',
            'client_name' => 'John Doe',
            'requested_on' => now(),
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('store_requisitions', [
            'requisition_id' => 'REQ456',
            'client_name' => 'John Doe',
            'created_by' => $user->id,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_creator()
    {
        $user = User::factory()->create();
        $storeRequisition = StoreRequisition::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $storeRequisition->creator);
        $this->assertEquals($user->id, $storeRequisition->creator->id);
    }

    /** @test */
    public function it_has_many_items()
    {
        $storeRequisition = StoreRequisition::factory()->create();
        $items = StoreItem::factory()->count(3)->create(['store_requisition_id' => $storeRequisition->requisition_id]);

        $this->assertCount(3, $storeRequisition->items);
        $this->assertInstanceOf(StoreItem::class, $storeRequisition->items->first());
    }
}
