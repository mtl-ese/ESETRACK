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
        Schema::create('store_requisition_destination_links', function (Blueprint $table) {
            $table->id();
            $table->string('store_requisition_id');
            $table->unsignedBigInteger('destination_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('store_requisition_id')
                ->references('requisition_id')
                ->on('store_requisitions')
                ->onDelete('cascade');

            $table->foreign('destination_id')
                ->references('id')
                ->on('store_requisition_destinations')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_requisition_destination_links');
    }
};
