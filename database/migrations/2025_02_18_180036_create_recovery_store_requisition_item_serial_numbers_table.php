<?php

use App\Models\RecoveryStoreRequisitionItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recovery_store_requisition_item_serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('serial_number');
            $table->timestamps();

            $table->foreign('item_id')
                ->references('id')
                ->on('recovery_store_requisition_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recovery_store_requisition_item_serial_numbers');
    }
};
