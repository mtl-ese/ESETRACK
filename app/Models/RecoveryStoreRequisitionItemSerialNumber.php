<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryStoreRequisitionItemSerialNumber extends Model

    /**
     * @property int $id
     * @property int $item_id
     * @property string $serial_number
     * @property int $returned
     */
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