<?php
// database/migrations/2025_02_04_000002_create_purchase_requisitions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseRequisitionsTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->string('requisition_id')->primary();
            $table->string('project_description');
            $table->unsignedBigInteger('created_by');
            $table->date('requested_on');
            $table->string('approved_by');
            $table->timestamps();

            // Foreign key
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_requisitions');
    }
}
