<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryStoreRequisition extends Model
{
    protected $primaryKey = 'recovery_store_requisition_id'; // Specify custom primary key
    public $incrementing = false; // Disable auto-increment if it's a string or UUID
    protected $keyType = 'string'; // Ensure it's treated as a string
    protected $fillable = [
        'recovery_store_requisition_id',
        'client_name',
        'location',
        'approved_by',
        'requested_on',
        'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function items()
    {
        return $this->hasMany(RecoveryStoreRequisitionItem::class,'recovery_requisition_id');
    }
}
