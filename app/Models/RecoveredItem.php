<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveredItem extends Model
{
    protected $fillable = [
        'item_name',
        'quantity',
        'store_return_id',
        'status',
        'balance'

    ];

    public function serial_numbers()
    {

        return $this->hasMany(RecoveredItemSerialNumber::class);
    }

    public function store_return(){
        return $this->belongsTo(StoreReturn::class);
    }
}
