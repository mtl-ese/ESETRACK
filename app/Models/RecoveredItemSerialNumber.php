<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveredItemSerialNumber extends Model
{
    protected $fillable=[
        'serial_numbers',
        'recovered_item_id'
    ];

    public function item(){

        return $this->belongsTo(RecoveredItem::class,'recovered_item_id');
    }
}
