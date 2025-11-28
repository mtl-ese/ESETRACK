<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryStoreSerialNumber extends Model
{
    protected $fillable = [
        'recovery_store_id',
        'serial_numbers'
    ];

    public function item()
    {
        return $this->belongsTo(RecoveryStore::class, 'recovery_store_id');
    }
}
