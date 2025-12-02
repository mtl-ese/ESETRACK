<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnsStoreSerialNumber extends Model
{
    protected $fillable=[
        'returns_store_id',
        'serial_numbers'
    ];

    public function item(){
        return $this->belongsTo(ReturnsStore::class,'returns_store_id');
    }
}
