<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryStoreSerialNumber extends Model

    /**
     * @property int $id
     * @property int $recovery_store_id
     * @property string $serial_numbers
     */
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
