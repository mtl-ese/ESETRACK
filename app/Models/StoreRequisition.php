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
        'requested_on',
        'created_by',
        'approved_by',
        'item_diversion_note'
    ];

    public function items()
    {
        return $this->hasMany(StoreItem::class, 'store_requisition_id', 'requisition_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recoveries()
    {
        return $this->hasMany(RecoveryStoreRequisition::class, 'store_requisition_id', 'requisition_id');
    }

    /**
     * Get all destination links for this requisition
     */
    public function destinationLinks()
    {
        return $this->hasMany(StoreRequisitionDestinationLink::class, 'store_requisition_id', 'requisition_id');
    }

    protected $casts = [
        'requested_on' => 'date'
    ];

}
