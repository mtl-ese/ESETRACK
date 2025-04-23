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
        Schema::create('recovery_store_requisitions', function (Blueprint $table) {
            $table->string('recovery_store_requisition_id')->primary();
            $table->string('client_name');
            $table->string('location');
            $table->date('requested_on');
            $table->unsignedBigInteger('created_by');
            $table->string('approved_by');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recovery_store_requisitions');
    }
};
