<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRequisitionDestinationItem extends Model
{
    protected $fillable = [
        'destination_link_id',
        'store_item_id',
        'quantity',
        'serials'
    ];

    protected $casts = [
        'serials' => 'array'
    ];

    /**
     * Get the destination link that owns this item
     */
    public function link()
    {
        return $this->belongsTo(StoreRequisitionDestinationLink::class, 'destination_link_id');
    }

    /**
     * Get the store item
     */
    public function item()
    {
        return $this->belongsTo(StoreItem::class, 'store_item_id');
    }
}
