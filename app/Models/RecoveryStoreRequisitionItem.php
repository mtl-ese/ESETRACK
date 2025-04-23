<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryStoreRequisitionItem extends Model
{
    protected $fillable=[
        'recovery_requisition_id',
        'item_name',
        'quantity'
    ];
    
    public function recovery_store_requisition()
    {
        return $this->belongsTo(RecoveryStoreRequisition::class, 'recovery_store_requisition_id', 'recovery_store_requisition_id');
    }
    public function serial_numbers()
    {
        return $this->hasMany(RecoveryStoreRequisitionItemSerialNumber::class,'item_id');
    }
}
