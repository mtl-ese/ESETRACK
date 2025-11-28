<?php
// app/Models/StoreItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreItem extends Model
{
    /** @use HasFactory<\Database\Factories\StoreItemFactory> */
    use HasFactory;
    protected $fillable = [
        'store_requisition_id',
        'item_name',
        'quantity'
    ];

    public function store_requisition()
    {
        return $this->belongsTo(StoreRequisition::class, 'store_requisition_id', 'requisition_id');
    }

    public function destinationItems()
    {
        return $this->hasMany(StoreRequisitionDestinationItem::class, 'store_item_id');
    }

    // StoreItem.php
    public function getDestinationInfoAttribute()
    {
        // If multiple destinations, you can join them
        if ($this->destinationItems && $this->destinationItems->count()) {
            return $this->destinationItems->map(function ($di) {
                return $di->link?->destination?->client . ' - ' . $di->link?->destination?->location;
            })->implode(', ');
        }
        return 'N/A';
    }

    public function serial_numbers()
    {
        return $this->hasMany(ItemSerialNumber::class, 'item_name', 'item_name');
    }

    // In StoreItem.php
    public function destinationLinks()
    {
        return $this->hasManyThrough(
            StoreRequisitionDestinationLink::class, // final model
            StoreRequisition::class,               // intermediate model
            'requisition_id',                                  // FK on intermediate (StoreRequisitionDestinationLink uses store_requisition_id)
            'store_requisition_id',                             // FK on final model
            'store_requisition_id',                             // local key on this table (StoreItem)
            'requisition_id'                                    // local key on intermediate (StoreRequisition)
        );
    }

}

