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
        Schema::create('emergency_returns', function (Blueprint $table) {
            $table->id();
            $table->string('emergency_requisition_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('approved_by');
            $table->date('returned_on');
            $table->timestamps();

            $table->foreign('emergency_requisition_id')
                ->references('requisition_id')
                ->on('emergency_requisitions')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_returns');
    }
};
