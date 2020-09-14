<?php

namespace gateway\app\gateway\control;

use gateway\app\gateway\models\OmnihubModel;

class Omnihub
{

    /**
     * index方法，用于默认入口
     *
     * @param array $request
     * @param array $response
     * @param array $app
     */
    public function index(&$request = array(), &$response = array(), &$app = array())
    {
        return $this->execute($request, $response, $app);
    }

    /**
     * Method execute
     * 转发请求
     *
     * @param array $request
     * @param array $response
     * @param array $app
     * @author xy.wu
     * @since 2020/3/27 17:35
     */
    public function execute(&$request = array(), &$response = array(), &$app = array())
    {
        $onmihubModel = new OmnihubModel();
        $result = $onmihubModel->execute($request);
        if (isset($result['code']) && !empty($result['code'])) {
            http_response_code($result['code']);
        }
        if (isset($result['status']) && $result['status'] == 'api-success' && isset($result['data']) && !empty($result['data'])) {
            $response = $result['data'];
        } else {
            $response = $result;
        }
    }
}


