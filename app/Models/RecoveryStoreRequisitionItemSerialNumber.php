<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryStoreRequisitionItemSerialNumber extends Model
{
    protected $fillable=[
        'serial_number',
        'item_id'
    ];

    public function item(){
        
        return $this->belongsTo(RecoveryStoreRequisitionItem::class,'item_id');
    }
}
