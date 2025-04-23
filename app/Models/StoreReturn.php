<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreReturn extends Model
{
    protected $fillable = [
        'store_requisition_id',
        'old_client',
        'location',
        'was_created_by',
        'created_by',
        'was_approved_by',
        'approved_by',
        'returned_on'
    ];

    public function store_requisition()
    {
        return $this->belongsTo(StoreRequisition::class, 'store_requisition_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function old_creator()
    {
        return $this->belongsTo(User::class, 'was_created_by');
    
    }

    public function items(){
        return $this->hasMany(RecoveredItem::class,'store_return_id');
    }
}



