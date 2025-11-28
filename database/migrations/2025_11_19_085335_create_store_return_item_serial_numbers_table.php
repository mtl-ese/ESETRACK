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
        Schema::create('store_return_item_serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_return_item_id');
            $table->unsignedBigInteger('item_serial_number_id');
            $table->timestamps();

            $table->foreign('store_return_item_id')
                ->references('id')
                ->on('store_return_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_return_item_serial_numbers');
    }
};
