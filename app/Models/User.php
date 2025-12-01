<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $employee_number
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PurchaseRequisition[] $PurchaseRequisitions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\StoreRequisition[] $StoreRequisitions
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'employee_number',
        'password',
        'profile_image',
        'last_login_at',
        'isAdmin',
        'isSuperAdmin',
        'isActivated',
        'is_active', // New column for active status
        'last_seen_at', // New column for last seen time
        'total_online_time', // New column for total online time in seconds
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'date',
            'last_login_at' => 'datetime',
            'isAdmin' => 'boolean',
            'isActivated' => 'boolean',
            'is_active' => 'boolean', // Cast for active status
            'last_seen_at' => 'datetime', // Cast for last seen time
        ];
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->password)) {
                $user->password = Hash::make('password'); // Set your default password here
            }
        });
    }

    // Relationships:
    public function PurchaseRequisitions()
    {
        return $this->hasMany(PurchaseRequisition::class, 'created_by');
    }

    public function StoreRequisitions()
    {
        return $this->hasMany(StoreRequisition::class, 'created_by');
    }

    public function RecoveryStoreRequisitions()
    {
        return $this->hasMany(RecoveryStoreRequisition::class, 'created_by');
    }

    public function role()
    {
        return $this->hasOne(UserRole::class);
    }

    public function onlineTimes()
    {
        return $this->hasMany(UserOnlineTime::class);
    }
}