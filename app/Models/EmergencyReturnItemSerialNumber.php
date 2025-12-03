<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $emergency_return_item_id
 * @property int $item_serial_number_id
 * @property-read \App\Models\EmergencyReturnItem $return_item
 * @property-read \App\Models\EmergencyRequisitionItemSerial $itemSerialNumber
 */
class EmergencyReturnItemSerialNumber extends Model
{
    protected $fillable = [
        'emergency_return_item_id',
        'item_serial_number_id',
    ];

    public function return_item()
    {
        return $this->belongsTo(EmergencyReturnItem::class, 'emergency_return_item_id');
    }

    public function itemSerialNumber()
    {
        return $this->belongsTo(EmergencyRequisitionItemSerial::class, 'item_serial_number_id');
    }
}
