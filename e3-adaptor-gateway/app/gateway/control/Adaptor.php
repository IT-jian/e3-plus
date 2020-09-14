<?php

namespace gateway\app\gateway\control;

use gateway\app\gateway\models\AdaptorModel;

class Adaptor
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
        $adaptorModel = new AdaptorModel();
        $result = $adaptorModel->execute($request);
        $header = getallheaders();

        $httpCode = $result['code'] ?? 400;
        http_response_code($httpCode);
        if (
            isset($result['status']) && $result['status'] == 'api-success' && isset($result['data']) && !empty($result['data'])
            && (!isset($header['Format']) || $header['Format'] != 'json')
        ) {
            $response = $result['data'];
        } else {
            $response = $result;
        }
    }
}


