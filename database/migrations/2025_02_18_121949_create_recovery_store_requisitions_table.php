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
        Schema::create('recovery_store_requisitions', function (Blueprint $table) {
            $table->id('recovery_requisition_id');
            $table->string('store_requisition_id');
            $table->date('recovered_on');
            $table->unsignedBigInteger('was_created_by');
            $table->unsignedBigInteger('created_by');
            $table->string('was_approved_by');
            $table->string('approved_by');
            $table->timestamps();

            $table->foreign('store_requisition_id')
                ->references('requisition_id')
                ->on('store_requisitions')
                ->cascadeOnDelete();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('was_created_by')->references('id')->on('users')->onDelete('cascade');
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
