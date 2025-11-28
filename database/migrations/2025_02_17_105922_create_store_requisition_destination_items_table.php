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
        Schema::create('store_requisition_destination_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('destination_link_id');
            $table->unsignedBigInteger('store_item_id');
            $table->unsignedInteger('quantity');
            $table->json('serials')->nullable(); // Optional serial numbers
            $table->timestamps();

            // Foreign keys
            $table->foreign('destination_link_id')
                ->references('id')
                ->on('store_requisition_destination_links')
                ->onDelete('cascade');

            $table->foreign('store_item_id')
                ->references('id')
                ->on('store_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_requisition_destination_items');
    }
};
