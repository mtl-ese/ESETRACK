<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('store_requisition_serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_serial_number_id')->constrained('item_serial_numbers')->onDelete('cascade');
            $table->string('store_requisition_id');
            $table->timestamps();

            $table->foreign('store_requisition_id')
                ->references('requisition_id')
                ->on('store_requisitions')
                ->OnDelete('cascade');

            $table->unique(['item_serial_number_id', 'store_requisition_id'], 'unique_serial_requisition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_requisition_serial_numbers');
    }
};
