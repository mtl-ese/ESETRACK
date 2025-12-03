<?php

use App\Models\ReturnsStore;
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
        Schema::create('returns_store_serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ReturnsStore::class)->constrained()->cascadeOnDelete();
            $table->string('serial_numbers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns_store_serial_numbers');
    }
};
