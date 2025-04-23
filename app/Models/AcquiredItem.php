<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcquiredItem extends Model
{
    protected $fillable = [
        'acquired_id',
        'balance',
        'item_description',
        'quantity'
    ];

    public function acquired()
    {
        return $this->belongsTo(Acquired::class);
    }
}
