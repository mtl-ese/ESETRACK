<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $emergency_requisition_id
 * @property int|null $created_by
 * @property string|null $approved_by
 * @property \Illuminate\Support\Carbon|null $returned_on
 * @property-read \App\Models\EmergencyRequisition $requisition
 * @property-read \App\Models\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmergencyReturnItem[] $items
 */
class EmergencyReturn extends Model
{
    protected $fillable = [
        'emergency_requisition_id',
        'created_by',
        'approved_by',
        'returned_on'
    ];

    public function requisition()
    {
        return $this->belongsTo(EmergencyRequisition::class, 'emergency_requisition_id', 'requisition_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(EmergencyReturnItem::class, 'emergency_return_id');
    }

    protected $casts = [
        'returned_on' => 'date'
    ];
}

