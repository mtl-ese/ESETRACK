<?php
// app/Models/StoreItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreItem extends Model
{
    /** @use HasFactory<\Database\Factories\StoreItemFactory> */
    use HasFactory;
    protected $fillable = [
        'store_requisition_id',
        'item_name',
        'quantity'
    ];

    public function store_requisition()
    {
        return $this->belongsTo(StoreRequisition::class, 'store_requisition_id', 'requisition_id');
    }
    public function serial_numbers()
    {
        return $this->hasMany(SerialNumber::class,'store_item_id');
    }

}
