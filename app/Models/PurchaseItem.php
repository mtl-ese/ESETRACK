<?php
// app/Models/purchaseItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseItem extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseItemFactory> */
    use HasFactory;

    protected $fillable = [
        'purchase_requisition_id',
        'item_description',
        'quantity'
    ];

    public function requisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id', 'requisition_id');
    }

    public function acquiredItems()
    {
        return $this->hasMany(AcquiredItem::class, 'purchase_item_id', 'id');
    }

    public function getTotalAcquiredAttribute()
    {
        return $this->acquiredItems()->sum('quantity');
    }

    public function getBalanceAttribute()
    {
        return $this->quantity - $this->total_acquired;
    }

}
