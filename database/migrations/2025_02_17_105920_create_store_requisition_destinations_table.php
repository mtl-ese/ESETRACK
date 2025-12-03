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
        Schema::create('store_requisition_destinations', function (Blueprint $table) {
            $table->id();
            $table->string('client');
            $table->string('location');
            $table->timestamps();

            // Unique constraint to prevent duplicate client-location pairs
            $table->unique(['client', 'location'], 'unique_client_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_requisition_destinations');
    }
};
