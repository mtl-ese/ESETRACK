<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $store_return_id
 * @property int|null $destination_link_id
 * @property string $item_name
 * @property int $quantity
 * @property int|null $balance
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\StoreReturnItemSerialNumber[] $serial_numbers
 */
class StoreReturnItem extends Model
{
    protected $fillable = [
        'store_return_id',
        'destination_link_id',
        'item_name',
        'quantity',
        'status',
        'balance'
    ];

    public function store_return()
    {
        return $this->belongsTo(StoreReturn::class);
    }

    public function serial_numbers()
    {
        return $this->hasMany(StoreReturnItemSerialNumber::class, 'store_return_item_id');
    }
    public function destinationLink()
    {
        return $this->belongsTo(StoreRequisitionDestinationLink::class, 'destination_link_id');
    }

}
