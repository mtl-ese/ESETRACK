<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcquiredItem extends Model
{
    protected $fillable = [
        'acquired_id',
        'purchase_item_id',
        'item_description',
        'quantity'
    ];

    public function acquired()
    {
        return $this->belongsTo(Acquired::class, 'acquired_id', 'id');
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id', 'id');
    }
}
