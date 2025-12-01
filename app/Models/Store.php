<?php
// app/Models/Store.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model

    /**
     * @property int $id
     * @property string $item_name
     * @property int $quantity
     */
{
    protected $fillable = ['item_name', 'quantity'];
}
