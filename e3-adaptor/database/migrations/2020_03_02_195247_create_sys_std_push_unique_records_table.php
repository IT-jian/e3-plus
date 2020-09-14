<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSysStdPushUniqueRecordsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_std_push_unique_record', function (Blueprint $table) {
            $table->string('bis_id', 50)->index()->comment('业务ID');
            $table->string('platform', 10)->comment('平台');
            $table->string('method', 50)->comment('类型');
            $table->primary(['method', 'platform', 'bis_id']);
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
        Schema::drop('sys_std_push_unique_record');
    }
}