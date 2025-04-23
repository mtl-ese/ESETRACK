<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmergencyReturn extends Model
{
    protected $fillable = [
        'emergency_requisition_id'
    ];

    public function requisition()
    {
        return $this
            ->belongsTo(EmergencyRequisition::class, 'emergency_requisition_id', 'requisition_id');
    }
}

