<?php
// database/migrations/2025_02_04_000003_create_purchase_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseItemsTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_requisition_id');
            $table->string('item_description');
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->foreign('purchase_requisition_id')
                ->references('requisition_id')->on('purchase_requisitions')
                ->onDelete('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('purchase_items');
    }
}
