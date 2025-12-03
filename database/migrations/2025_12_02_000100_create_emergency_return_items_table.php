<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('emergency_return_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emergency_return_id');
            $table->string('item_name');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('balance')->nullable();
            $table->timestamps();

            $table->foreign('emergency_return_id')
                ->references('id')
                ->on('emergency_returns')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_return_items');
    }
};
