<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisition extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseRequisitionFactory> */
    use HasFactory;
    protected $primaryKey = 'requisition_id';
    public $incrementing = false;
    protected $fillable = [
        'requisition_id',
        'project_description',
        'requested_on',
        'created_by',
        'approved_by'
    ];

    protected $casts = [
        'requested_on' => 'date'
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_requisition_id', 'requisition_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acquired()
    {
        return $this->hasMany(Acquired::class, 'purchase_requisition_id', 'requisition_id');
    }

}
