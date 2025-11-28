<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreRequisitionSerialNumber extends Model
{
    protected $fillable = [
        'item_serial_number_id',
        'store_requisition_id'
    ];

    public function itemSerialNumber(): BelongsTo
    {
        return $this->belongsTo(ItemSerialNumber::class, 'item_serial_number_id');
    }

    public function storeRequisition(): BelongsTo
    {
        return $this->belongsTo(StoreRequisition::class, 'store_requisition_id', 'requisition_id');
    }
}
