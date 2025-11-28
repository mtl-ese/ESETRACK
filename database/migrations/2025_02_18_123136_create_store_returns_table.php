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
        Schema::create('store_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recovery_requisition_id');
            $table->date('returned_on');
            $table->unsignedBigInteger('created_by');
            $table->string('approved_by');
            $table->timestamps();

            $table->foreign('recovery_requisition_id')
                ->references('recovery_requisition_id')
                ->on('recovery_store_requisitions')
                ->cascadeOnDelete();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_returns');
    }
};
