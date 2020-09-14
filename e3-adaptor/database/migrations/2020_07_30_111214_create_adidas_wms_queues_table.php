<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdidasWmsQueuesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adidas_wms_queue', function (Blueprint $table) {
            $table->integer('id', true)->comment('ID');
            $table->unsignedBigInteger('bis_id', 50)->comment('业务ID');
            $table->string('wms', 20)->comment('wms名称');
            $table->string('method', 50)->comment('类型');
            $table->unsignedTinyInteger('status')->default(0)->comment('状态');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('adidas_wms_queue');
    }
}
