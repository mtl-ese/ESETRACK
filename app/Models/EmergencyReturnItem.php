<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $emergency_return_id
 * @property string $item_name
 * @property int $quantity
 * @property int|null $balance
 * @property-read \App\Models\EmergencyReturn $returns
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EmergencyReturnItemSerialNumber[] $serial_numbers
 */
class EmergencyReturnItem extends Model
{

    protected $fillable = [
        'emergency_return_id',
        'item_name',
        'quantity',
        'balance',
    ];

    public function returns()
    {
        return $this->belongsTo(EmergencyReturn::class, 'emergency_return_id');
    }

    public function serial_numbers()
    {
        return $this->hasMany(EmergencyReturnItemSerialNumber::class, 'emergency_return_item_id');
    }
}
