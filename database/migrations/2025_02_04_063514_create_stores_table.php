<?php

// database/migrations/2025_02_04_000006_create_stores_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('item_name')->unique();
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('stores');
    }
}
