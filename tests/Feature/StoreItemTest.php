<?php

namespace Tests\Unit;

use App\Models\StoreItem;
use App\Models\StoreRequisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_store_item()
    {
        $user = User::factory()->create();
        
        $storeRequisition = StoreRequisition::factory()->create([
            'requisition_id' => 'REQ456',
            'client_name' => 'John Doe',
            'requested_on' => now(),
            'created_by' => $user->id,
        ]);
        
        $storeItem = StoreItem::factory()->create([
            'store_requisition_id' => 'REQ456',
            'item_name' => 'Laptop',
            'serial_number' => 'SN123456',
        ]);

        $this->assertDatabaseHas('store_items', [
            'store_requisition_id' => $storeItem->store_requisition_id,
            'item_name' => 'Laptop',
            'serial_number' => 'SN123456',
        ]);
    }

    /** @test */
    public function it_belongs_to_a_store_requisition()
    {
        $user = User::factory()->create();
        
        $storeRequisition = StoreRequisition::factory()->create([
            'requisition_id' => 'REQ456',
            'client_name' => 'John Doe',
            'requested_on' => now(),
            'created_by' => $user->id,
        ]);
        
        $storeItem = StoreItem::factory()->create([
            'store_requisition_id'=>$storeRequisition->requisition_id,
        ]);

        $this->assertInstanceOf(StoreRequisition::class, $storeItem->storeRequisition);
        $this->assertEquals($storeRequisition->requisition_id, $storeItem->store_requisition_id);
    }
}
