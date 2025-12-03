<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmergencyRequisitionItemSerial extends Model
{
    protected $fillable = [
        'item_id',
        'serial_number',
        'returned'
    ];

    public function item()
    {
        return $this
            ->belongsTo(EmergencyRequisitionItem::class, 'item_id', 'id');
    }

    protected $casts = [
        'returned' => 'boolean',
    ];
}

