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
        Schema::create('emergency_requisition_item_serials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('serial_number');
            $table->boolean('returned')->default(false);
            $table->timestamps();

            //foreign
            $table->foreign('item_id')
                ->references('id')
                ->on('emergency_requisition_items')
                ->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_requisition_item_serials');
    }
};
