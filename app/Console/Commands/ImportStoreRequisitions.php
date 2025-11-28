<?php

namespace App\Console\Commands;

use Carbon\Carbon;

use Illuminate\Console\Command;

class ImportStoreRequisitions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:store-requisitions';

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
        $csvPath = storage_path('app/public/STORE REQUISITIONS FINAL LIST.csv');

        if (!file_exists($csvPath)) {
            $this->error("CSV file not found!");
            return;
        }

        $csv = \League\Csv\Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0); // First row as header

        foreach ($csv as $row) {
            \App\Models\StoreRequisition::create([
                'requisition_id' => $row['requisition_id'],
                'client_name' => $row['client_name'],
                'location' => $row['location'] ? $row['location'] : null,
                'requested_on' => Carbon::createFromFormat('d/m/Y', $row['requested_on'])->format('Y-m-d H:i:s'), // Explicitly parse d/m/Y format
                'created_by' => 5, // Assuming created_by is always 1 for this example
                'approved_by' => 'Thokozani',
                'created_at' => Carbon::createFromFormat('d/m/Y', $row['requested_on'])->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::createFromFormat('d/m/Y', $row['requested_on'])->format('Y-m-d H:i:s'),
            ]);
        }

        $this->info('Store requisitions imported successfully!');
    }
}