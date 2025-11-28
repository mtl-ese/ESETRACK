<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'first_name' => 'Richard',
            'last_name' => 'Kondowe',
            'email' => 'richie@mtl.mw',
            'password' => Hash::make('richie123'),
            'employee_number' => 1001,
            'isAdmin' => 0,
            'isSuperAdmin' => 1,
            'isActivated' => 1,
            'is_active' => 1,
        ]);

        User::create([
            'first_name' => 'Lasmon',
            'last_name' => 'kapota',
            'email' => 'lasmon@mtl.mw',
            'password' => Hash::make('lasmon123'),
            'employee_number' => 1002,
            'isAdmin' => 1,
            'isSuperAdmin' => 0,
            'isActivated' => 1,
            'is_active' => 1,
        ]);

        User::create([
            'first_name' => 'Taonga',
            'last_name' => 'Tseka Phiri',
            'email' => 'taonga@mtl.mw',
            'password' => Hash::make('taonga123'),
            'employee_number' => 1003,
            'isAdmin' => 0,
            'isSuperAdmin' => 0,
            'isActivated' => 1,
            'is_active' => 1,
        ]);
    }
}
