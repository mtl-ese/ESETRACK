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
        Schema::create('recovery_store_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recovery_requisition_id');
            $table->unsignedBigInteger('store_item_id')->nullable();
            $table->unsignedBigInteger('destination_link_id');
            $table->string('item_name');
            $table->Integer('quantity')->default(0);
            $table->integer('returned_quantity')->default(0);
            $table->Integer('balance')->default(0);
            $table->timestamps();

            $table->foreign('recovery_requisition_id')
                ->references('recovery_requisition_id')
                ->on('recovery_store_requisitions')
                ->onDelete('cascade');

            $table->foreign('store_item_id')
                ->references('id')
                ->on('store_items')
                ->onDelete('cascade');

            $table->foreign('destination_link_id')
                ->references('id')
                ->on('store_requisition_destination_links')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recovery_store_requisition_items');
    }
};
