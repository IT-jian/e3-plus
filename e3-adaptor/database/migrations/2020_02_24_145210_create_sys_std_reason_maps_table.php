<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSysStdReasonMapsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_std_reason_map', function (Blueprint $table) {
            $table->unsignedInteger('id', true)->comment('ID');
            $table->string('platform', 10)->comment('平台');
            $table->string('type', 50)->comment('类型');
            $table->string('source_name', 255)->comment('平台原因');
            $table->string('map_name', 255)->nullable()->comment('映射原因');
            $table->string('remark', 255)->nullable()->comment('备注');
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
        Schema::drop('sys_std_reason_map');
    }
}