<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnsStore extends Model
{
    protected $fillable = [
        'id',
        'item_name',
        'quantity',
    ];

    public function serial_numbers()
    {
        return $this->hasMany(ReturnsStoreSerialNumber::class);
    }
}