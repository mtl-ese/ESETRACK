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
        return $this->belongsTo(PurchaseRequisition::class);
    }

    
}
