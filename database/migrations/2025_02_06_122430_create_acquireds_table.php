<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('acquireds', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_requisition_id')->unique();
            $table->timestamps();

            $table->foreign('purchase_requisition_id')->references('requisition_id')->on('purchase_requisitions')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acquired_items');
    }
};
