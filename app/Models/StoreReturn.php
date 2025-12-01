<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $recovery_requisition_id
 * @property int|null $created_by
 * @property string|null $approved_by
 * @property \Illuminate\Support\Carbon|null $returned_on
 * @property-read \App\Models\RecoveryStoreRequisition $recovery_store_requisition
 * @property-read \App\Models\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\StoreReturnItem[] $items
 */
class StoreReturn extends Model
{
    protected $fillable = [
        'recovery_requisition_id',
        'created_by',
        'approved_by',
        'returned_on'
    ];

    public function recovery_store_requisition()
    {
        return $this->belongsTo(RecoveryStoreRequisition::class, 'recovery_requisition_id', 'recovery_requisition_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(StoreReturnItem::class, 'store_return_id');
    }

    protected function casts(): array
    {
        return [
            'returned_on' => 'date'
        ];
    }
}



