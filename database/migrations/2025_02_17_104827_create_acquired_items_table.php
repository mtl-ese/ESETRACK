<?php

use App\Models\Acquired;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('acquired_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('acquired_id');
            $table->string('item_description');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('balance');
            $table->timestamps();

            $table->foreign('acquired_id')
                ->references('id')
                ->on('acquireds')
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
