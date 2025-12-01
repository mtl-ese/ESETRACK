<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $store_return_item_id
 * @property int $item_serial_number_id
 * @property-read \App\Models\RecoveryStoreRequisitionItemSerialNumber $itemSerialNumber
 */
class StoreReturnItemSerialNumber extends Model
{
    protected $fillable = [
        'store_return_item_id',
        'item_serial_number_id',
    ];

    public function item()
    {
        return $this->belongsTo(StoreReturnItem::class, 'store_return_item_id');
    }

    public function itemSerialNumber()
    {
        return $this->belongsTo(RecoveryStoreRequisitionItemSerialNumber::class, 'item_serial_number_id');
    }
}
