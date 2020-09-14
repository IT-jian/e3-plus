<?php


namespace App\Services\Hub\Adidas\Request\Transformer;


/**
 * 天猫库存同步结果异步通知
 *
 * Class SkuInventoryUpdateAckTransformer
 * @package App\Services\Hub\Adidas\Request\Transformer
 *
 * @author linqihai
 * @since 2020/8/23 21:50
 */
class SkuInventoryUpdateAckTransformer extends BaseTransformer
{
    const STATUS_INIT = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_LOCK = 2;
    const STATUS_FAIL_RETRY = 3; // 失败重试
    const STATUS_FAIL_BREAK = 4; // 失败不重试
    const STATUS_FAIL_UNKNOWN = 5; // 未知失败，不重试

    public function format($log)
    {
        $responseStatus = self::STATUS_SUCCESS == $log['response_status'] ? '1' : '0';
        $retry = '0';
        if (self::STATUS_FAIL_UNKNOWN == $log['response_status']) {
            $retry = '';
        } else if (self::STATUS_FAIL_BREAK == $log['response_status']){
            $retry = '0';
        } else if (self::STATUS_FAIL_RETRY == $log['response_status']){
            $retry = '1';
        }
        $platformResponseMap = [];
        foreach ($log['notice_content'] as $item) {
            if (isset($item['sku_id'])) {
                $platformResponseMap[$item['sku_id']] = $item;
            }
        }
        $data = [];
        foreach ($log['skus'] as $sku) {
            $responseSku = $platformResponseMap[$sku['sku_id']] ?? [];
            if ($responseSku) {// 成功的
                $item = [
                    'Platform_sku_id' => $sku['sku_id'],
                    'Platform_Response' => $responseStatus,
                    'modified' => $responseSku['modified'] ,
                    'quantity' => $responseSku['quantity'] ,
                    'outer_id' => $sku['outer_id'] ,
                    'retry' => $retry ,
                ];
            } else {
                $item = [
                    'Platform_sku_id' => $sku['sku_id'],
                    'Platform_Response' => '0',
                    'modified' => $sku['updated_at'] ,
                    'quantity' => $sku['quantity'] ,
                    'outer_id' => $sku['outer_id'] ,
                    'retry' => '1' ,
                ];
            }
            if (0 == $responseStatus && data_get($log, 'response.code', 0)) {
                $item['sub_code'] = data_get($log, 'response.sub_code', '');
                $item['msg'] = data_get($log, 'response.msg', '');
            }
            $data[] = $item;
        }

        $result = [
            'status' => 'api-success',
            'code' => 200,
            'type' => (int)$log['update_type'],
            'data' => [
                'responses' => $data
            ]
        ];

        return json_encode($result);
    }
}
