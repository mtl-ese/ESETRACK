<?php
// app/Models/StoreRequisition.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreRequisition extends Model
{
    /** @use HasFactory<\Database\Factories\StoreRequisitionFactory> */
    use HasFactory;
    protected $primaryKey = 'requisition_id'; // Specify custom primary key
    public $incrementing = false; // Disable auto-increment if it's a string or UUID
    protected $keyType = 'string'; // Ensure it's treated as a string
    protected $fillable = [
        'requisition_id', 
        'client_name', 
        'location',
        'requested_on', 
        'created_by',
        'approved_by'
    ];

    public function items()
    {
        return $this->hasMany(StoreItem::class,'store_requisition_id', 'requisition_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function return(){
        return $this->hasOne(StoreReturn::class,'store_requisition_id');
    }

}
