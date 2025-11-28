<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StoreItem;
use App\Models\SerialNumber;

class ImportStoreRequisitionItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:store-requisition-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $csvPath = storage_path('app/public/store_requisition_items.csv');

        if (!file_exists($csvPath)) {
            $this->error("CSV file not found!");
            return;
        }

        $csv = \League\Csv\Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0); // First row as header

        foreach ($csv as $row) {
            // Create the store item
            $storeItem = StoreItem::create([
                'store_requisition_id' => $row['requisition_id'],
                'item_name' => $row['item_name'],
                'quantity' => $row['quantity']
            ]);

            // Handle serial numbers if provided
            if (!empty($row['serial_numbers'])) {
                $serialNumbers = explode('|', $row['serial_numbers']); // Split serial numbers by delimiter
                foreach ($serialNumbers as $serialNumber) {
                    SerialNumber::create([
                        'store_item_id' => $storeItem->id, // Associate with the created store item
                        'store_requisition_id' => $row['requisition_id'],
                        'serial_number' => $serialNumber
                    ]);
                }
            }
        }

        $this->info('Store requisition items and serial numbers imported successfully!');
    }
}