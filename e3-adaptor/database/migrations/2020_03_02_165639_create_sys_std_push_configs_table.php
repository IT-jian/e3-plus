<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSysStdPushConfigsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_std_push_config', function (Blueprint $table) {
            $table->integer('id', true)->comment('ID');
            $table->string('method', 50)->unique()->comment('类型');
            $table->string('proxy', 20)->nullable()->comment('推送代理：qimen');
            $table->string('on_queue', 50)->nullable()->comment('指定推送队列，未指定默认为：sys_std_push_hub');
            $table->unsignedTinyInteger('stop_push')->default(1)->comment('停止推送：0：正常推送  1：停止推送');
            $table->unsignedTinyInteger('request_once')->default(0)->comment('仅允许推送一次：0 可以多次推送 1 推送成功后不再推送。当仅允许一次，推送成功之后将写入 record 表');
            $table->unsignedInteger('try_times')->nullable()->comment('尝试轮次');
            $table->unsignedInteger('tries')->nullable()->comment('尝试次数');
            $table->unsignedInteger('retry_after')->nullable()->comment('每轮尝试之间的间隔，单位 秒，必须大于 delay');
            $table->unsignedInteger('delay')->nullable()->comment('每次尝试之间的间隔，单位 秒');
            $table->unsignedInteger('push_sort')->default(1)->comment('推送顺序，从小到大推送');
            $table->timestamps();
        });

        $configs = [
            ['method' => \App\Services\Hub\HubRequestEnum::TRADE_CREATE, 'request_once' => 1, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::TRADE_CANCEL, 'request_once' => 0, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::TRADE_ADDRESS_MODIFY, 'request_once' => 0, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::REFUND_CREATE, 'request_once' => 1, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::REFUND_RETURN_CREATE_EXTEND, 'request_once' => 1, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::REFUND_CANCEL, 'request_once' => 0, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::REFUND_RETURN_CREATE, 'request_once' => 1, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::REFUND_RETURN_CANCEL, 'request_once' => 0, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::REFUND_RETURN_LOGISTIC_MODIFY, 'request_once' => 0, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::EXCHANGE_CREATE, 'request_once' => 1, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::EXCHANGE_CREATE_EXTEND, 'request_once' => 1, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::EXCHANGE_CANCEL, 'request_once' => 0, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::EXCHANGE_RETURN_LOGISTIC_MODIFY, 'request_once' => 0, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::STEP_TRADE_CREATE, 'request_once' => 0, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::STEP_TRADE_PAID, 'request_once' => 0, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::STEP_TRADE_CANCEL, 'request_once' => 0, 'try_times' => 3, 'tries' => 3, 'retry_after' => 20 * 60, 'delay' => 10, 'proxy' => null],
            ['method' => \App\Services\Hub\HubRequestEnum::SKU_INVENTORY_UPDATE_ACK, 'request_once' => 0, 'try_times' => 3, 'tries' => 3, 'retry_after' => 3 * 60, 'delay' => 10, 'proxy' => null],
        ];
        \App\Models\SysStdPushConfig::insert($configs);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sys_std_push_config');
    }
}
