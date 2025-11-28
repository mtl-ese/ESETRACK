<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryStoreRequisitionItemSerialNumber extends Model
{
    protected $fillable = [
        'item_id',
        'serial_number',
        'returned'
    ];

    public function item()
    {
        return $this->belongsTo(RecoveryStoreRequisitionItem::class, 'item_id');
    }

    protected $casts = [
        'returned' => 'boolean',
    ];
}