<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRequisitionDestinationLink extends Model
{
    protected $fillable = [
        'store_requisition_id',
        'destination_id'
    ];

    /**
     * Get the requisition that owns this link
     */
    public function requisition()
    {
        return $this->belongsTo(StoreRequisition::class, 'store_requisition_id', 'requisition_id');
    }

    /**
     * Get the destination for this link
     */
    public function destination()
    {
        return $this->belongsTo(StoreRequisitionDestination::class, 'destination_id');
    }

    /**
     * Get all items for this destination link
     */
    public function items()
    {
        return $this->hasMany(StoreRequisitionDestinationItem::class, 'destination_link_id');
    }
}
