<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryStore extends Model

    /**
     * @property int $id
     * @property string $item_name
     * @property int $quantity
     * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RecoveryStoreSerialNumber[] $serial_numbers
     */
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
