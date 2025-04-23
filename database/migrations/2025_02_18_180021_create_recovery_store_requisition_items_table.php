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
            $table->string('recovery_requisition_id');
            $table->string('item_name');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->foreign('recovery_requisition_id')
                ->references('recovery_store_requisition_id')
                ->on('recovery_store_requisitions')
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
