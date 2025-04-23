<?php
// database/migrations/2025_02_04_000005_create_store_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreItemsTable extends Migration
{
    public function up()
    {
        Schema::create('store_items', function (Blueprint $table) {
            $table->id();
            $table->string('store_requisition_id');
            $table->string('item_name');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->foreign('store_requisition_id')
                ->references('requisition_id')->on('store_requisitions')
                ->onDelete('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('store_items');
    }
}
