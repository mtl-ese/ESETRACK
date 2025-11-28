<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemSerialNumber extends Model
{
    protected $fillable = [
        'serial_number',
        'item_name'
    ];

    public function storeRequisitionHistory(): HasMany
    {
        return $this->hasMany(StoreRequisitionSerialNumber::class, 'item_serial_number_id');
    }
}
