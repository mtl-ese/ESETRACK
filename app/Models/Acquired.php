<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acquired extends Model
{
    /** @use HasFactory<\Database\Factories\AcquiredItemFactory> */
    use HasFactory;
    
    protected $table='acquireds';
    protected $fillable = [
        'id',
        'purchase_requisition_id',
    ];
    public function requisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function items()
    {
        return $this->hasMany(AcquiredItem::class);
    }
}
