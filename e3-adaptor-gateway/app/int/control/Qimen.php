<?php
/*
 * File name
 * 
 * @author xy.wu
 * @since 2020/4/3 17:22
 */

namespace gateway\app\int\control;

use gateway\app\int\models\QimenModel;
use gateway\boot\CommonTool;

class Qimen
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
        $qimenModel = new QimenModel();
        $result = $qimenModel->execute($request);

        $logFileName = 'qimen';
        $msg = "Request: " . var_export($request, true) . "\tResult: " . var_export($result, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>Request=");
            print_r($request);
            print_r("<hr/>Result=");
            print_r($result);
        }

        $response = $result['data'];
    }

    /**
     * Method render
     * 自定义格式化返回结果
     *
     * @param array $request
     * @param array $response
     * @param array $app
     * @author xy.wu
     * @since 2020/4/3 22:32
     */
//    public function render(&$request = array(), &$response = array(), &$app = array())
//    {
//
//    }
}


