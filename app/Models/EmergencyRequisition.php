<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmergencyRequisition extends Model
{
    protected $primaryKey = 'requisition_id'; // Specify custom primary key
    public $incrementing = false; // Disable auto-increment if it's a string or UUID
    protected $keyType = 'string'; // Ensure it's treated as a string
    protected $fillable = [
        'requisition_id',
        'initiator',
        'department',
        'created_by',
        'approved_by',
        'returned_on'
    ];

    protected function casts(): array
    {
        return [
            'returned_on' => 'datetime',
        ];
    }

    public function items()
    {
        return $this->hasMany(EmergencyRequisitionItem::class, 'emergency_requisition_id', 'requisition_id');
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function return()
    {
        return $this->hasOne(EmergencyReturn::class,'emergency_requisition_id','requisition_id');
    }

}


