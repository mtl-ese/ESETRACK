<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\RecoveryStoreRequisition;
use App\Models\RecoveryStoreRequisitionItemSerialNumber;

class RecoveryStoreRequisitionItem extends Model
    /**
     * @property int $id
     * @property int $item_id
     * @property int $recovery_requisition_id
     * @property string $item_name
     * @property int $quantity
     * @property int|null $returned_quantity
     * @property int|null $destination_link_id
     * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RecoveryStoreRequisitionItemSerialNumber[] $serial_numbers
     */
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
