<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $requisition_id
 * @property string $initiator
 * @property string $department
 * @property int|null $created_by
 * @property string|null $approved_by
 * @property \Illuminate\Support\Carbon|null $requested_on
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmergencyRequisitionItem[] $items
 */
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
        'requested_on',
    ];

    protected function casts(): array
    {
        return [
            'requested_on' => 'date',
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

    public function returns()
    {
        return $this->hasMany(EmergencyReturn::class, 'emergency_requisition_id', 'requisition_id');
    }

}


