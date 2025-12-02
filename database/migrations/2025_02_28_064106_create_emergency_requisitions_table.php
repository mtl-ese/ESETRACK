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
        Schema::create('emergency_requisitions', function (Blueprint $table) {
            $table->string('requisition_id')->primary();
            $table->string('initiator');
            $table->string('department');
            $table->unsignedBigInteger('created_by');
            $table->string('approved_by');
            $table->date('requested_on')->nullable();
            $table->timestamp('returned_on')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_requisitions');
    }
};
