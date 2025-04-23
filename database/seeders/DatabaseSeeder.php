<?php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            // You can also seed some purchase and store requisitions:
        ]);

        // Example: Create 5 purchase requisitions with items
        \App\Models\PurchaseItem::factory(5)
            ->create();


        \App\Models\AcquiredItem::factory(5)
            ->create();

        // Example: Create 5 store requisitions with items
        \App\Models\StoreItem::factory(5)
            ->create();
    }
}
