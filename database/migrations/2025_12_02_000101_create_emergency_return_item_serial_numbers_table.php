<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('emergency_return_item_serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emergency_return_item_id');
            $table->unsignedBigInteger('item_serial_number_id');
            $table->timestamps();
        });
        Schema::table('emergency_return_item_serial_numbers', function (Blueprint $table) {
            $table->foreign('emergency_return_item_id', 'er_item_sn_fk')
                ->references('id')
                ->on('emergency_return_items')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_return_item_serial_numbers');
    }
};
