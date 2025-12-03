<?php

use App\Models\User;
use App\Models\EmergencyRequisition;
use App\Models\EmergencyRequisitionItem;

it('shows the emergency return form with a requisition datalist', function () {
    $user = User::factory()->create();

    // create a requisition with department and initiator and at least one item so it appears in the list
    $requisition = EmergencyRequisition::create([
        'requisition_id' => 'MTL-100',
        'initiator' => 'Alfred Chisale',
        'department' => 'NOC',
        'created_by' => $user->id,
        'approved_by' => 'Approver',
        'requested_on' => now()->toDateString(),
    ]);

    EmergencyRequisitionItem::create([
        'emergency_requisition_id' => 'MTL-100',
        'item_name' => 'Test Item',
        'quantity' => 1,
        'from' => 'Store',
        'same_to_return' => 0,
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/emergency/return/create');

    $response->assertOk();
    $response->assertSee('id="requisitions-list"');
    // the label should contain Department (Initiator) as configured in the view
    $response->assertSee('NOC (Alfred Chisale)');
});

it('validates return_date and returns validation errors when missing', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/emergency/return/create')
        ->post('/emergency/return/store', [
            'requisition_id' => 'some-id'
            // return_date intentionally omitted to ensure validation triggers
        ]);

    $response->assertRedirect('/emergency/return/create');
    $response->assertSessionHasErrors(['return_date']);
});

it('creates emergency_return with items when submitting warehouse return form', function () {
    $user = User::factory()->create();

    // create a requisition with an item (simple item without serials)
    $requisitionId = 'ER-TEST-100';
    $requisition = \App\Models\EmergencyRequisition::create([
        'requisition_id' => $requisitionId,
        'initiator' => 'Tester',
        'department' => 'QA',
        'created_by' => $user->id,
        'approved_by' => 'Approver',
        'requested_on' => now()->toDateString(),
    ]);

    \App\Models\EmergencyRequisitionItem::create([
        'emergency_requisition_id' => $requisitionId,
        'item_name' => 'Test Cable',
        'quantity' => 2,
        'from' => 'Stock',
        'same_to_return' => 0,
    ]);

    $date = now()->toDateString();

    $response = $this
        ->actingAs($user)
        ->post('/emergency/return/store', [
            'requisition_id' => $requisitionId,
            'approved_by' => 'Approver',
            'return_date' => $date,
            'quantities' => ['Test Cable' => 2],
        ]);

    $response->assertRedirect(route('return.index'));

    // assert emergency_returns has record and requisition returned_on updated
    $this->assertDatabaseHas('emergency_returns', [
        'emergency_requisition_id' => $requisitionId,
        'returned_on' => $date,
    ]);

    $this->assertDatabaseHas('emergency_requisition_items', [
        'emergency_requisition_id' => $requisitionId,
        'item_name' => 'Test Cable',
        'returned_quantity' => 2,
    ]);

    $this->assertDatabaseHas('emergency_return_items', [
        'item_name' => 'Test Cable',
        'quantity' => 2,
    ]);
});
