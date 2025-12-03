<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\RecoveryStore;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recovery_store_serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(RecoveryStore::class)->constrained()->cascadeOnDelete();
            $table->string('serial_numbers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recovery_store_serial_numbers');
    }
};
