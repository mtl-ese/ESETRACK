<?php

use App\Models\StoreItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StoreItem::class)->constrained()->onDelete('cascade');
            $table->string('store_requisition_id');
            $table->json('serial_number');
            $table->timestamps();

            $table
                ->foreign('store_requisition_id')
                ->references('requisition_id')
                ->on('store_requisitions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serial_numbers');
    }
};
