<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialNumber extends Model
{
    protected $fillable = [
        'serial_number',
        'store_item_id',
        'store_requisition_id'
    ];

    public function item()
    {

        return $this->belongsTo(StoreItem::class, 'store_item_id');
    }

    public function requisition()
    {
        return $this->belongsTo(StoreRequisition::class, 'store_requisition_id');
    }

    protected $casts = [
        'serial_number' => 'array'
    ];
}