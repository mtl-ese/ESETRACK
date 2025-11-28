<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StoreRequisitionDestination;

class RecoveryStoreRequisition extends Model
{
    protected $primaryKey = 'recovery_requisition_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public function getKeyName(): string
    {
        return 'recovery_requisition_id';
    }
    protected $fillable = [
        'store_requisition_id',
        'was_created_by',
        'created_by',
        'was_approved_by',
        'approved_by',
        'recovered_on'
    ];

    public function store_requisition()
    {
        return $this->belongsTo(StoreRequisition::class, 'store_requisition_id', 'requisition_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function destinationLink()
    {
        return $this->belongsTo(StoreRequisitionDestinationLink::class, 'destination_link_id');
    }

    public function old_creator()
    {
        return $this->belongsTo(User::class, 'was_created_by');

    }

    public function items()
    {
        return $this->hasMany(RecoveryStoreRequisitionItem::class, 'recovery_requisition_id', 'recovery_requisition_id');
    }

    protected function casts(): array
    {
        return [
            'recovered_on' => 'date'
        ];
    }
}
