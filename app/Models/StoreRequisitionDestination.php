<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRequisitionDestination extends Model
{
    protected $fillable = [
        'client',
        'location'
    ];

    /**
     * Get all requisition links for this destination
     */
    public function links()
    {
        return $this->hasMany(StoreRequisitionDestinationLink::class, 'destination_id');
    }


}
