<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->comment('商店编码');
            $table->string('name')->comment('商店名称');
            $table->string('platform')->default('taobao')->comment('来源类型');
            $table->string('app_key')->default('')->comment('app key');
            $table->string('app_url')->default('')->comment('请求地址');
            $table->string('app_secret')->default('')->comment('app secret');
            $table->string('seller_nick')->default('')->comment('平台店铺名称');
            $table->string('access_token')->default('')->comment('访问 token');
            $table->string('refresh_token')->default('')->comment('刷新 token');
            $table->integer('token_expired_at')->nullable()->comment('过期时间');
            $table->text('extends')->nullable()->comment('平台个性字段');
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
        Schema::dropIfExists('shops');
    }
}
