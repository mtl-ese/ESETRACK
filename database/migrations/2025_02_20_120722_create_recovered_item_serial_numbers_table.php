<?php

use App\Models\RecoveredItem;
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
        Schema::create('recovered_item_serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(RecoveredItem::class)->constrained()->cascadeOnDelete();
            $table->string('serial_numbers')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recovered_item_serial_numbers');
    }
};
