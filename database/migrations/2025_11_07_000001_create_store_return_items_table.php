<?php

use App\Models\StoreReturn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('store_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StoreReturn::class)->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('destination_link_id')->nullable();
            $table->string('item_name');
            $table->integer('quantity')->default(0);
            $table->string('status')->nullable();
            $table->integer('balance')->default(0);
            $table->timestamps();

            $table->foreign('destination_link_id')
                ->references('id')
                ->on('store_requisition_destination_links')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_return_items');
    }
};
