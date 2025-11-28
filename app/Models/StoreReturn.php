<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

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



