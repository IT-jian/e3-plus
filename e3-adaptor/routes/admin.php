<?php
// 1. 请求前缀 admin，2. 处理跨域和后台日志中间件
$router->group(['prefix' => 'admin', 'middleware' => ['cors', 'operate_log']], function () use ($router) {
    $router->post('login', 'AuthenticateController@login');
    $router->post('logout', ['middleware' => 'auth', 'uses' => 'AuthenticateController@logout']);
    // .env 编辑文件
    $router->get('dotenv', 'Admin\EnvController@index');
    $router->post('dotenv', 'Admin\EnvController@store');
    $router->get('dotenv/appkey', function () { // 生成APPKEY
        $new = '';
        $key = config('app.key');
        if (empty($key)) { // 生成 APP_KEY
            $key = 'base64:' . base64_encode(\Illuminate\Encryption\Encrypter::generateKey(config('app.cipher')));
            $this->env->changeEnv(
                ['APP_KEY' => $key]
            );
            \Illuminate\Support\Env::getVariables()->set('APP_KEY', $key);
            $new = config('app.key');
        }

        return [$new, $key];
    });
    $router->get('/metrics/jobs/{id}', 'Admin\JobMetricsController@show');
    // 1. 权限中间件
    $router->group(['middleware' => 'auth', 'namespace' => 'Admin'], function () use ($router) {
        $router->get('user/profile', 'UserController@profile');
        $router->get('user', 'UserController@index');
        $router->post('user', 'UserController@store');
        $router->get('user/{id}', 'UserController@show');
        $router->put('user/{id}', 'UserController@update');
        $router->patch('user/{id}', 'UserController@update');
        $router->delete('user/{id}', 'UserController@destroy');
        // 角色，每个路由单独设置权限
        $router->get('role', ['middleware' => 'permission:view_role', 'uses' => 'RoleController@index']);
        $router->post('role', ['middleware' => 'permission:add_role', 'uses' => 'RoleController@store']);
        $router->get('role/{id}', ['middleware' => 'permission:view_role', 'uses' => 'RoleController@show']);
        $router->put('role/{id}', ['middleware' => 'permission:edit_role', 'uses' => 'RoleController@update']);
        $router->patch('role/{id}', ['middleware' => 'permission:edit_role', 'uses' => 'RoleController@update']);
        $router->delete('role/{id}', ['middleware' => 'permission:delete_role', 'uses' => 'RoleController@destroy']);
        // 权限
        $router->get('permission', 'PermissionController@index');
        $router->post('permission', 'PermissionController@store');
        $router->get('permission/{id}', 'PermissionController@show');
        $router->put('permission/{id}', 'PermissionController@update');
        $router->patch('permission/{id}', 'PermissionController@update');
        $router->delete('permission/{id}', 'PermissionController@destroy');
        // 操作日志
        $router->get('operation_log', 'OperationLogController@index');
        $router->delete('operation_log/{id}', 'OperationLogController@destroy');
        // 其他
        $router->get('common/permissions_tree', 'PermissionController@tree');
        $router->get('common/role_select_options', 'CommonController@roleSelectOptions');
        $router->get('common/role_permission_ids/{id}', 'CommonController@rolePermissionIds');
        // passport
        $router->get('oauth/tokens', '\Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController@forUser');

        // 来源平台
        $router->get('platform', 'PlatformController@index');
        $router->post('platform', 'PlatformController@store');
        $router->get('platform/{id}', 'PlatformController@show');
        $router->put('platform/{id}', 'PlatformController@update');
        $router->patch('platform/{id}', 'PlatformController@update');
        $router->delete('platform/{id}', 'PlatformController@destroy');

        // 商店列表，新增，编辑，刷新token，获取token，token 回调
        $router->get('shop', 'ShopController@index');
        $router->post('shop', 'ShopController@store');
        $router->get('shop/{id}', 'ShopController@show');
        $router->put('shop/{id}', 'ShopController@update');
        $router->patch('shop/{id}', 'ShopController@update');
        $router->delete('shop/{id}', 'ShopController@destroy');
        // 商店token 管理
        $router->get('shop_token/call/{id}', 'ShopTokenController@callToken');
        $router->get('shop_token/refresh/{id}', 'ShopTokenController@refreshToken');
        // 订单列表
        // 淘宝订单
        $router->get('taobao_trade/fetch', ['middleware' => 'permission:view_taobao_trade', 'uses' => 'TaobaoTradeController@fetch']); // 根据条件同步指定的范围的单据
        $router->get('taobao_trade/transfer', ['middleware' => 'permission:view_taobao_trade', 'uses' => 'TaobaoTradeController@transfer']); // 将淘宝表转为标准表
        $router->get('taobao_trade', ['middleware' => 'permission:view_taobao_trade', 'uses' => 'TaobaoTradeController@index']);
        // 淘宝退单路由
        $router->get('taobao_refund/fetch', ['middleware' => 'permission:view_taobao_refund', 'uses' => 'TaobaoRefundController@fetch']);
        $router->get('taobao_refund/transfer', ['middleware' => 'permission:view_taobao_refund', 'uses' => 'TaobaoRefundController@transfer']);
        $router->get('taobao_refund', ['middleware' => 'permission:view_taobao_refund', 'uses' => 'TaobaoRefundController@index']);
        // 淘宝换货单路由
        $router->get('taobao_exchange/fetch', ['middleware' => 'permission:view_taobao_exchange', 'uses' => 'TaobaoExchangeController@fetch']); // 根据条件同步指定的范围的单据
        $router->get('taobao_exchange/transfer', ['middleware' => 'permission:view_taobao_exchange', 'uses' => 'TaobaoExchangeController@transfer']); // 将淘宝表转为标准表
        $router->get('taobao_exchange', ['middleware' => 'permission:view_taobao_exchange', 'uses' => 'TaobaoExchangeController@index']);
        // 淘宝商品路由
        $router->get('taobao_item/fetch', ['middleware' => 'permission:view_taobao_item', 'uses' => 'TaobaoItemController@fetch']);
        $router->get('taobao_item/transfer', ['middleware' => 'permission:view_taobao_item', 'uses' => 'TaobaoItemController@transfer']);
        $router->get('taobao_item', ['middleware' => 'permission:view_taobao_item', 'uses' => 'TaobaoItemController@index']);

        // 淘宝订单评论路由
        $router->get('taobao_comment/fetch', ['middleware' => 'permission:view_taobao_comment', 'uses' => 'TaobaoCommentController@fetch']);
        $router->get('taobao_comment/export', ['middleware' => 'permission:view_taobao_comment', 'uses' => 'TaobaoCommentController@export']); //导出CSV文件
        $router->get('taobao_comment', ['middleware' => 'permission:view_taobao_comment', 'uses' => 'TaobaoCommentController@index']);

        // 淘宝开票申请路由
        $router->get('taobao_invoice_apply/fetch', ['middleware' => 'permission:view_taobao_invoice_apply', 'uses' => 'TaobaoInvoiceApplyController@fetch']); // 查询开票申请详情
        $router->get('taobao_invoice_apply/detail', ['middleware' => 'permission:view_taobao_invoice_apply', 'uses' => 'TaobaoInvoiceApplyController@detail']); // 查询发票明细
        $router->get('taobao_invoice_apply/push', ['middleware' => 'permission:view_taobao_invoice_apply', 'uses' => 'TaobaoInvoiceApplyController@push']); // 开票
        $router->get('taobao_invoice_apply', ['middleware' => 'permission:view_taobao_invoice_apply', 'uses' => 'TaobaoInvoiceApplyController@index']);

        // 京东订单列表路由
        $router->get('jingdong_trade/fetch', ['middleware' => 'permission:view_jingdong_trade', 'uses' => 'JingdongTradeController@fetch']); // 根据条件同步指定的范围的单据
        $router->get('jingdong_trade/transfer', ['middleware' => 'permission:view_jingdong_trade', 'uses' => 'JingdongTradeController@transfer']); // 将京东表转为标准表
        $router->get('jingdong_trade', ['middleware' => 'permission:view_jingdong_trade', 'uses' => 'JingdongTradeController@index']);

        // 京东订单金额分摊路由
        $router->get('jingdong_order_split_amount/fetch', ['middleware' => 'permission:view_jingdong_order_split_amount', 'uses' => 'JingdongOrderSplitAmountController@fetch']); // 根据条件同步指定的范围的单据
        $router->get('jingdong_order_split_amount/transfer', ['middleware' => 'permission:view_jingdong_order_split_amount', 'uses' => 'JingdongOrderSplitAmountController@transfer']); // 将京东表转为标准表
        $router->get('jingdong_order_split_amount', ['middleware' => 'permission:view_jingdong_order_split_amount', 'uses' => 'JingdongOrderSplitAmountController@index']);

        // 京东预售订单路由
        $router->get('jingdong_step_trade/fetch', ['middleware' => 'permission:view_jingdong_step_trade', 'uses' => 'JingdongStepTradeController@fetch']); // 根据条件同步指定的范围的单据
        $router->get('jingdong_step_trade/transfer', ['middleware' => 'permission:view_jingdong_step_trade', 'uses' => 'JingdongStepTradeController@transfer']); // 将京东表转为标准表
        $router->get('jingdong_step_trade', ['middleware' => 'permission:view_jingdong_step_trade', 'uses' => 'JingdongStepTradeController@index']);

        // 京东退款申请路由
        $router->get('jingdong_refund_apply/fetch', ['middleware' => 'permission:view_jingdong_refund_apply', 'uses' => 'JingdongRefundApplyController@fetch']);
        $router->get('jingdong_refund_apply', ['middleware' => 'permission:view_jingdong_refund_apply', 'uses' => 'JingdongRefundApplyController@index']);

        // 京东退单路由
        $router->get('jingdong_refund/fetch', ['middleware' => 'permission:view_jingdong_refund', 'uses' => 'JingdongRefundController@fetch']);
        $router->get('jingdong_refund/transfer', ['middleware' => 'permission:view_jingdong_refund', 'uses' => 'JingdongRefundController@transfer']);
        $router->get('jingdong_refund', ['middleware' => 'permission:view_jingdong_refund', 'uses' => 'JingdongRefundController@index']);

        // 京东商品评论路由
        $router->get('jingdong_comment/fetch', ['middleware' => 'permission:view_jingdong_comment', 'uses' => 'JingdongCommentController@fetch']);
        $router->get('jingdong_comment/export', ['middleware' => 'permission:view_jingdong_comment', 'uses' => 'JingdongCommentController@export']); //导出CSV文件
        $router->get('jingdong_comment', ['middleware' => 'permission:view_jingdong_comment', 'uses' => 'JingdongCommentController@index']);

        // 京东商品路由
        $router->get('jingdong_item/fetch', ['middleware' => 'permission:view_jingdong_item', 'uses' => 'JingdongItemController@fetch']);
        $router->get('jingdong_item/transfer', ['middleware' => 'permission:view_jingdong_item', 'uses' => 'JingdongItemController@transfer']);
        $router->get('jingdong_item', ['middleware' => 'permission:view_jingdong_item', 'uses' => 'JingdongItemController@index']);

        // 京东平台SKU路由
        $router->get('jingdong_sku/fetch', ['middleware' => 'permission:view_jingdong_sku', 'uses' => 'JingdongSkuController@fetch']);
        $router->get('jingdong_sku', ['middleware' => 'permission:view_jingdong_sku', 'uses' => 'JingdongSkuController@index']);

        // 标准订单路由
        $router->get('sys_std_trade', ['middleware' => 'permission:view_sys_std_trade', 'uses' => 'SysStdTradeController@index']);
        // 标准订单明细路由
        $router->get('sys_std_trade_item', ['middleware' => 'permission:view_sys_std_trade_item', 'uses' => 'SysStdTradeItemController@index']);
        // 标准订单促销路由
        $router->get('sys_std_trade_promotion', ['middleware' => 'permission:view_sys_std_trade_promotion', 'uses' => 'SysStdTradePromotionController@index']);

        // 标准退单路由
        $router->get('sys_std_refund', ['middleware' => 'permission:view_sys_std_refund', 'uses' => 'SysStdRefundController@index']);
        // 标准退单明细路由
        $router->get('sys_std_refund_item', ['middleware' => 'permission:view_sys_std_refund_item', 'uses' => 'SysStdRefundItemController@index']);
        // 标准平台sku表
        $router->get('sys_std_platform_sku/export', ['middleware' => 'permission:view_sys_std_platform_sku', 'uses' => 'SysStdPlatformSkuController@export']);
        $router->get('sys_std_platform_sku/push/{sku_id}', ['middleware' => 'permission:view_sys_std_platform_sku', 'uses' => 'SysStdPlatformSkuController@push']);
        $router->get('sys_std_platform_sku/push_format/{sku_id}', ['middleware' => 'permission:view_sys_std_platform_sku', 'uses' => 'SysStdPlatformSkuController@pushFormat']);
        $router->get('sys_std_platform_sku', ['middleware' => 'permission:view_sys_std_platform_sku', 'uses' => 'SysStdPlatformSkuController@index']);

        // 标准换货单路由
        $router->get('sys_std_exchange', ['middleware' => 'permission:view_sys_std_exchange', 'uses' => 'SysStdExchangeController@index']);
        // 标准换货明细路由
        $router->get('sys_std_exchange_item', ['middleware' => 'permission:view_sys_std_exchange_item', 'uses' => 'SysStdExchangeItemController@index']);
        // HubApi日志路由
        $router->get('hub_api_log', ['middleware' => 'permission:view_hub_api_log', 'uses' => 'HubApiLogController@index']);
        // HubClient日志路由
        $router->get('hub_client_log', ['middleware' => 'permission:view_hub_client_log', 'uses' => 'HubClientLogController@index']);

        // 订单 hub client 接口
        $router->get('sys_std_trade/push/{id}', ['middleware' => 'permission:view_sys_std_trade', 'uses' => 'SysStdTradeClientController@push']);
        $router->get('sys_std_trade/push_format/{id}', ['middleware' => 'permission:view_sys_std_trade', 'uses' => 'SysStdTradeClientController@pushFormat']);
        $router->get('sys_std_trade/push_cancel/{id}', ['middleware' => 'permission:view_sys_std_trade', 'uses' => 'SysStdTradeClientController@pushCancel']);
        $router->get('sys_std_trade/push_cancel_format/{id}', ['middleware' => 'permission:view_sys_std_trade', 'uses' => 'SysStdTradeClientController@pushCancelFormat']);
        $router->get('sys_std_trade/push_step_cancel/{id}', ['middleware' => 'permission:view_sys_std_trade', 'uses' => 'SysStdTradeClientController@pushStepCancel']);
        $router->get('sys_std_trade/push_step_paid/{id}', ['middleware' => 'permission:view_sys_std_trade', 'uses' => 'SysStdTradeClientController@pushStepPaid']);
        $router->get('sys_std_trade/push_step_paid_format/{id}', ['middleware' => 'permission:view_sys_std_trade', 'uses' => 'SysStdTradeClientController@pushStepPaidFormat']);
        $router->get('sys_std_trade/push_step_cancel_format/{id}', ['middleware' => 'permission:view_sys_std_trade', 'uses' => 'SysStdTradeClientController@pushStepCancelFormat']);
        $router->get('sys_std_trade/push_address_modify/{id}', ['middleware' => 'permission:view_sys_std_trade', 'uses' => 'SysStdTradeClientController@pushAddressModify']);
        $router->get('sys_std_trade/push_address_modify_format/{id}', ['middleware' => 'permission:view_sys_std_trade', 'uses' => 'SysStdTradeClientController@pushAddressModifyFormat']);
        // 退单 订单hub client 接口
        $router->get('sys_std_refund/push/{id}', ['middleware' => 'permission:view_sys_std_refund', 'uses' => 'SysStdRefundClientController@push']);
        $router->get('sys_std_refund/push_format/{id}', ['middleware' => 'permission:view_sys_std_refund', 'uses' => 'SysStdRefundClientController@pushFormat']);
        $router->get('sys_std_refund/push_cancel/{id}', ['middleware' => 'permission:view_sys_std_refund', 'uses' => 'SysStdRefundClientController@pushCancel']);
        $router->get('sys_std_refund/push_cancel_format/{id}', ['middleware' => 'permission:view_sys_std_refund', 'uses' => 'SysStdRefundClientController@pushCancelFormat']);
        $router->get('sys_std_refund/push_logistic/{id}', ['middleware' => 'permission:view_sys_std_refund', 'uses' => 'SysStdRefundClientController@pushLogistic']);
        $router->get('sys_std_refund/push_logistic_format/{id}', ['middleware' => 'permission:view_sys_std_refund', 'uses' => 'SysStdRefundClientController@pushLogisticFormat']);
        // 换货单 hub client 接口
        $router->get('sys_std_exchange/push/{dispute_id}', ['middleware' => 'permission:view_sys_std_exchange', 'uses' => 'SysStdExchangeClientController@push']);
        $router->get('sys_std_exchange/push_format/{dispute_id}', ['middleware' => 'permission:view_sys_std_exchange', 'uses' => 'SysStdExchangeClientController@pushFormat']);
        $router->get('sys_std_exchange/push_cancel/{dispute_id}', ['middleware' => 'permission:view_sys_std_exchange', 'uses' => 'SysStdExchangeClientController@pushCancel']);
        $router->get('sys_std_exchange/push_cancel_format/{dispute_id}', ['middleware' => 'permission:view_sys_std_exchange', 'uses' => 'SysStdExchangeClientController@pushCancelFormat']);
        $router->get('sys_std_exchange/push_logistic/{dispute_id}', ['middleware' => 'permission:view_sys_std_exchange', 'uses' => 'SysStdExchangeClientController@pushLogistic']);
        $router->get('sys_std_exchange/push_logistic_format/{dispute_id}', ['middleware' => 'permission:view_sys_std_exchange', 'uses' => 'SysStdExchangeClientController@pushLogisticFormat']);


        // 任务队列配置路由
        $router->get('queue_worker_config', ['middleware' => 'permission:view_queue_worker_config', 'uses' => 'QueueWorkerConfigController@index']);
        $router->post('queue_worker_config', ['middleware' => 'permission:add_queue_worker_config', 'uses' => 'QueueWorkerConfigController@store']);
        $router->get('queue_worker_config/{queue_worker_configs}', ['middleware' => 'permission:view_queue_worker_config', 'uses' => 'QueueWorkerConfigController@show']);
        $router->put('queue_worker_config/{queue_worker_configs}', ['middleware' => 'permission:edit_queue_worker_config', 'uses' => 'QueueWorkerConfigController@update']);
        $router->patch('queue_worker_config/{queue_worker_configs}', ['middleware' => 'permission:edit_queue_worker_config', 'uses' => 'QueueWorkerConfigController@update']);
        $router->delete('queue_worker_config/{queue_worker_configs}', ['middleware' => 'permission:delete_queue_worker_config', 'uses' => 'QueueWorkerConfigController@destroy']);

        // 推送队列路由
        $router->get('sys_std_push_queue', ['middleware' => 'permission:view_sys_std_push_queue', 'uses' => 'SysStdPushQueueController@index']);
        $router->post('sys_std_push_queue', ['middleware' => 'permission:add_sys_std_push_queue', 'uses' => 'SysStdPushQueueController@store']);
        $router->get('sys_std_push_queue/{sys_std_push_queues}', ['middleware' => 'permission:view_sys_std_push_queue', 'uses' => 'SysStdPushQueueController@show']);
        $router->put('sys_std_push_queue/{sys_std_push_queues}', ['middleware' => 'permission:edit_sys_std_push_queue', 'uses' => 'SysStdPushQueueController@update']);
        $router->patch('sys_std_push_queue/{sys_std_push_queues}', ['middleware' => 'permission:edit_sys_std_push_queue', 'uses' => 'SysStdPushQueueController@update']);
        $router->delete('sys_std_push_queue/{sys_std_push_queues}', ['middleware' => 'permission:delete_sys_std_push_queue', 'uses' => 'SysStdPushQueueController@destroy']);
        // 推送按钮
        $router->get('sys_std_push_queue_action/format/{id}', ['middleware' => 'permission:view_sys_std_push_queue', 'uses' => 'SysStdPushQueueActionController@pushFormat']);
        $router->get('sys_std_push_queue_action/{id}', ['middleware' => 'permission:view_sys_std_push_queue', 'uses' => 'SysStdPushQueueActionController@push']);
        $router->post('sys_std_push_queue_action', ['middleware' => 'permission:view_sys_std_push_queue', 'uses' => 'SysStdPushQueueActionController@pushBatch']);
        // 原因映射路由
        $router->get('sys_std_reason_map', ['middleware' => 'permission:view_sys_std_reason_map', 'uses' => 'SysStdReasonMapController@index']);
        $router->post('sys_std_reason_map', ['middleware' => 'permission:add_sys_std_reason_map', 'uses' => 'SysStdReasonMapController@store']);
        $router->get('sys_std_reason_map/{sys_std_reason_maps}', ['middleware' => 'permission:view_sys_std_reason_map', 'uses' => 'SysStdReasonMapController@show']);
        $router->put('sys_std_reason_map/{sys_std_reason_maps}', ['middleware' => 'permission:edit_sys_std_reason_map', 'uses' => 'SysStdReasonMapController@update']);
        $router->patch('sys_std_reason_map/{sys_std_reason_maps}', ['middleware' => 'permission:edit_sys_std_reason_map', 'uses' => 'SysStdReasonMapController@update']);
        $router->delete('sys_std_reason_map/{sys_std_reason_maps}', ['middleware' => 'permission:delete_sys_std_reason_map', 'uses' => 'SysStdReasonMapController@destroy']);

        // 推送下发配置路
        $router->get('sys_std_push_config', ['middleware' => 'permission:view_sys_std_push_config', 'uses' => 'SysStdPushConfigController@index']);
        $router->post('sys_std_push_config', ['middleware' => 'permission:add_sys_std_push_config', 'uses' => 'SysStdPushConfigController@store']);
        $router->get('sys_std_push_config/{sys_std_push_configs}', ['middleware' => 'permission:view_sys_std_push_config', 'uses' => 'SysStdPushConfigController@show']);
        $router->put('sys_std_push_config/{sys_std_push_configs}', ['middleware' => 'permission:edit_sys_std_push_config', 'uses' => 'SysStdPushConfigController@update']);
        $router->patch('sys_std_push_config/{sys_std_push_configs}', ['middleware' => 'permission:edit_sys_std_push_config', 'uses' => 'SysStdPushConfigController@update']);
        $router->delete('sys_std_push_config/{sys_std_push_configs}', ['middleware' => 'permission:delete_sys_std_push_config', 'uses' => 'SysStdPushConfigController@destroy']);


        // 平台单据下载配置路由
        $router->get('platform_download_config', ['middleware' => 'permission:view_platform_download_config', 'uses' => 'PlatformDownloadConfigController@index']);
        $router->post('platform_download_config', ['middleware' => 'permission:add_platform_download_config', 'uses' => 'PlatformDownloadConfigController@store']);
        $router->get('platform_download_config/{platform_download_configs}', ['middleware' => 'permission:view_platform_download_config', 'uses' => 'PlatformDownloadConfigController@show']);
        $router->put('platform_download_config/{platform_download_configs}', ['middleware' => 'permission:edit_platform_download_config', 'uses' => 'PlatformDownloadConfigController@update']);
        $router->patch('platform_download_config/{platform_download_configs}', ['middleware' => 'permission:edit_platform_download_config', 'uses' => 'PlatformDownloadConfigController@update']);
        $router->delete('platform_download_config/{platform_download_configs}', ['middleware' => 'permission:delete_platform_download_config', 'uses' => 'PlatformDownloadConfigController@destroy']);

        // 店铺下载配置路由
        $router->get('shop_download_config', ['middleware' => 'permission:view_shop_download_config', 'uses' => 'ShopDownloadConfigController@index']);
        $router->post('shop_download_config', ['middleware' => 'permission:add_shop_download_config', 'uses' => 'ShopDownloadConfigController@store']);
        $router->get('shop_download_config/{shop_download_configs}', ['middleware' => 'permission:view_shop_download_config', 'uses' => 'ShopDownloadConfigController@show']);
        $router->put('shop_download_config/{shop_download_configs}', ['middleware' => 'permission:edit_shop_download_config', 'uses' => 'ShopDownloadConfigController@update']);
        $router->patch('shop_download_config/{shop_download_configs}', ['middleware' => 'permission:edit_shop_download_config', 'uses' => 'ShopDownloadConfigController@update']);
        $router->delete('shop_download_config/{shop_download_configs}', ['middleware' => 'permission:delete_shop_download_config', 'uses' => 'ShopDownloadConfigController@destroy']);

        // Adidas商家编码路由
        $router->get('adidas_item', ['middleware' => 'permission:view_adidas_item', 'uses' => 'AdidasItemController@index']);
        $router->post('adidas_item', ['middleware' => 'permission:add_adidas_item', 'uses' => 'AdidasItemController@store']);
        $router->get('adidas_item/{adidas_items}', ['middleware' => 'permission:view_adidas_item', 'uses' => 'AdidasItemController@show']);
        $router->put('adidas_item/{adidas_items}', ['middleware' => 'permission:edit_adidas_item', 'uses' => 'AdidasItemController@update']);
        $router->patch('adidas_item/{adidas_items}', ['middleware' => 'permission:edit_adidas_item', 'uses' => 'AdidasItemController@update']);
        $router->delete('adidas_item/{adidas_items}', ['middleware' => 'permission:delete_adidas_item', 'uses' => 'AdidasItemController@destroy']);
        // adidas 模拟请求
        $router->get('adidas_request_simulation', ['uses' => 'AdidasRequestSimulationController@index']); // 默认值
        $router->post('adidas_request_simulation', ['uses' => 'AdidasRequestSimulationController@store']);
        // 清除缓存
        $router->post('cache_clear', 'CacheClearController@index');

        // Adidas Wms Queue路由
        $router->get('adidas_wms_queue', ['middleware' => 'permission:view_adidas_wms_queue', 'uses' => 'AdidasWmsQueueController@index']);

        // AdidasWms推送日志路由
        $router->get('adidas_wms_client_log', ['middleware' => 'permission:view_adidas_wms_client_log', 'uses' => 'AdidasWmsClientLogController@index']);

        // 库存更新日志路由
        $router->get('sku_inventory_api_log', ['middleware' => 'permission:view_sku_inventory_api_log', 'uses' => 'SkuInventoryApiLogController@index']);

        // 淘宝库存同步队列路由
        $router->get('taobao_skus_quantity_update_queue', ['middleware' => 'permission:view_taobao_skus_quantity_update_queue', 'uses' => 'TaobaoSkusQuantityUpdateQueueController@index']);

        // 库存同步平台日志路由
        $router->get('sku_inventory_platform_log', ['middleware' => 'permission:view_sku_inventory_platform_log', 'uses' => 'SkuInventoryPlatformLogController@index']);

    });
});


// token 回调地址
$router->get('shop_token/callback/{platform}', 'Admin\ShopTokenController@callbackToken');
