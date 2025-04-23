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
            $table->timestamps();

            $table->foreign('emergency_requisition_id')
                ->references('requisition_id')
                ->on('emergency_requisitions')
                ->onDelete('cascade');
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
