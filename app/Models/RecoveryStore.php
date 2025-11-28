<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryStore extends Model
{
    protected $fillable = [
        'id',
        'item_name',
        'quantity',
    ];
    public function serial_numbers()
    {
        return $this->hasMany(RecoveryStoreSerialNumber::class, 'recovery_store_id');
    }
}
