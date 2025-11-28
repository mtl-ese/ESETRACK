<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RecoveryStoreRequisition;
use App\Models\RecoveryStoreRequisitionItemSerialNumber;

class RecoveryStoreRequisitionItem extends Model
{
    protected $fillable = [
        'recovery_requisition_id',
        'store_item_id',
        'destination_link_id',
        'item_name',
        'quantity',
        'returned_quantity',
        'balance'
    ];

    public function serial_numbers()
    {
        return $this->hasMany(RecoveryStoreRequisitionItemSerialNumber::class, 'item_id');
    }

    /**
     * Alias for serial_numbers() to support camelCase access
     */
    public function serialNumbers()
    {
        return $this->serial_numbers();
    }

    public function store_item()
    {
        return $this->belongsTo(StoreItem::class, 'store_item_id', 'id');
    }

    public function recovery_store_requisition()
    {
        // Belongs to recovery requisition identified by the string key
        return $this->belongsTo(RecoveryStoreRequisition::class, 'recovery_requisition_id', 'recovery_requisition_id');
    }

    protected $casts = [
        'quantity' => 'integer',
        'returned_quantity' => 'integer',
    ];
    /*public function getBalanceAttribute()
    {
        return $this->quantity - $this->returned_quantity;
    }*/

    public function destinationLink()
    {
        return $this->belongsTo(StoreRequisitionDestinationLink::class, 'destination_link_id');
    }

}
