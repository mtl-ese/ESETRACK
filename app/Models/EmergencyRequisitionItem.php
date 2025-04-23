<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmergencyRequisitionItem extends Model
{
    protected $fillable = [
        'emergency_requisition_id',
        'item_name',
        'quantity',
        'from',
        'same_to_return',
    ];

    public function requisition()
    {
        return $this->belongsTo(EmergencyRequisition::class, 'emergency_requisition_id', 'requisition_id');
    }

    public function serial_numbers()
    {
        return $this->hasMany(EmergencyRequisitionItemSerial::class, 'item_id');
    }
}