<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdidasItemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adidas_items', function (Blueprint $table) {
            $table->integer('id', true)->comment('ID');
            $table->string('outer_sku_id', 50)->comment('Outer Sku Id');
            $table->string('item_id', 50)->comment('Item Id');
            $table->string('size', 50)->comment('Size');
            $table->timestamps();

            $table->unique('outer_sku_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('adidas_items');
    }
}