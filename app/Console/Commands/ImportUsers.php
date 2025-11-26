<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use League\Csv\Reader;

class ImportUsers extends Command
{
    protected $signature = 'import:users';
    protected $description = 'Import users from a CSV file';

    public function handle()
    {
        $csvPath = storage_path('app/public/users.csv');

        if (!file_exists($csvPath)) {
            $this->error("CSV file not found!");
            return;
        }

        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0); // First row as header

        foreach ($csv as $row) {
            User::create([
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'],
                'password' => Hash::make('password'), // Hash new passwords
                'DOB' => $row['DOB'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'isAdmin' => $row['isAdmin'],
                'isSuperAdmin' => $row['isSuperAdmin'],
                'isActivated' => $row['isActivated'],
            ]);
        }

        $this->info('Users imported successfully!');
    }
}
