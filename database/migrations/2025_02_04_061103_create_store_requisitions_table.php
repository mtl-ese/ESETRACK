<?php
// database/migrations/2025_02_04_000004_create_store_requisitions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreRequisitionsTable extends Migration
{
    public function up()
    {
        Schema::create('store_requisitions', function (Blueprint $table) {
            $table->string('requisition_id')->primary();
            $table->string('client_name');
            $table->string('location');
            $table->date('requested_on');
            $table->unsignedBigInteger('created_by');
            $table->string('approved_by');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('store_requisitions');
    }
}
