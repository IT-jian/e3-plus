<?php
/*
* File name
* 
* @author xy.wu
* @since 2020/3/27 14:37
*/

namespace gateway\app\int\models;

use gateway\app\CommonModel;
use gateway\app\gateway\models\AdaptorModel;
use gateway\boot\CommonTool;

if (!defined("TOP_SDK_WORK_DIR")) {
    define('TOP_SDK_WORK_DIR', __DIR__  . "/../../../runtime/logs/");
}
require_once __DIR__ . "/../../../library/taobao_sdk/TopSdk.php";

/**
 * Class QimenModel
 * 提供给奇门调用的地址
 *
 * @package gateway\app\int\models
 * @author xy.wu
 * @since 2020/4/3 22:34
 */
class QimenModel extends CommonModel
{
    /**
     * 生产环境地址
     *
     * @var string
     */
    protected $gatewayUrl = 'https://eco.taobao.com/router/rest';

    /**
     * Method execute
     * 执行转发请求
     *
     * @param array $request
     * @return array|false|mixed|string
     * @author xy.wu
     * @since 2020/3/27 15:06
     */
    public function execute($request = array())
    {
        /**
         * Array
        (
        [customerid] => adidas
        [app_key] => 30291088
        [target_appkey] => 30291088
        [method] => qimen.taobao.pos.weborder.sync
        [sign_method] => md5
        [format] => json
        [sign] => ADA4A946E23C2277D7C344547FF43500
        [request_id] => 8ih3lzvo92w6
        [source_appkey] => 12491455
        [timestamp] => 2020-06-30 21:28:48
        [data] =>
        {"method":"e3plus.oms.logistics.offline.send","timestamp":1574264582,"shop_code":"EMC1","deal_code":"714462209340192864","shipping_code":"SF","shipping_sn":"288803971148","deal_type":"1","is_split":1,"sub_deal_code":"714462209341192864,714462209342192864"}
        )
         *
         * /int/qimen/index/?customerid=adidas&app_key=30291088&target_appkey=30291088&method=qimen.taobao.pos.weborder.sync&sign_method=md5&format=json&sign=ADA4A946E23C2277D7C344547FF43500&request_id=8ih3lzvo92w6&source_appkey=12491455&timestamp=2020-06-30+21%3A28%3A48
         */
//        print_r($request);

        $this->request = $request;
        //记录日志
        $this->logFileName = $logFileName = 'qimen';

        $header = getallheaders();
        $msg = "Request: " . var_export($request, true) . "\tHeader: " . var_export($header, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>Request=");
            print_r($request);
            print_r("<hr/>Header=");
            print_r($header);
        }

        if (!isset($request['app_key']) || empty($request['app_key']) || $request['app_key'] != CommonTool::loadEnv('qimen.app_key')) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Not Allowed app_key[' . $request['app_key'] . ']', 'data' => array());
            $return['data'] = array('flag' => 'failure', 'code' => 'api-invalid-parameter-app_key', 'message' => $return['message']);
            return $return;
        }

        if (!isset($request['method']) || empty($request['method']) || !in_array($request['method'], array('qimen.taobao.pos.weborder.sync', 'qimen.taobao.pos.order.return.add'))) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Not Allowed method[' . $request['method'] . ']', 'data' => array());
            $return['data'] = array('flag' => 'failure', 'code' => 'api-invalid-parameter-method', 'message' => $return['message']);
            return $return;
        }

        $params = $request;
        unset($params['sign'], $params['data']);

        $sign = $this->generateSign($params);
        $msg = "Sign: " . var_export($sign, true) . "\tQimenSign: " . var_export($request['sign'], true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>Sign=");
            print_r($sign);
            print_r("<hr/>QimenSign=");
            print_r($request['sign']);
        }

        if ($sign != $request['sign'] && (!isset($request['__skip*sign__']) || $request['__skip*sign__'] != 1)) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Sign Error', 'data' => array());
            $return['data'] = array('flag' => 'failure', 'code' => 'api-invalid-parameter-sign_error', 'message' => $return['message']);
            return $return;
        }

        $content = json_decode($request['data'], true);
        $msg = "Content: " . var_export($content, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>Content=");
            print_r($content);
        }

        $extendContent = json_decode($content['extend_props'], true);
        $msg = "ExtendContent: " . var_export($extendContent, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>ExtendContent=");
            print_r($extendContent);
        }

        $interfaceType = isset($extendContent['type']) ? $extendContent['type'] : '';
        if (!in_array($interfaceType, array('tradeCreate', 'refundReturnCreate', 'exchangeCreate'))) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Not Allowed extend_props_type[' . $extendContent['type'] . ']', 'data' => array());
            $return['data'] = array('flag' => 'failure', 'code' => 'api-invalid-parameter-extend_props_type', 'message' => $return['message']);
            return $return;
        }

        if (!isset($extendContent['content']) || empty($extendContent['content'])) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Missing Required extend_props_content', 'data' => array());
            $return['data'] = array('flag' => 'failure', 'code' => 'api-invalid-parameter-extend_props_content', 'message' => $return['message']);
            return $return;
        }

        //解密
        $xml = $this->deal_decrypt($extendContent);

        $msg = "Xml: " . var_export($xml, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>Xml=");
            print_r($xml);
        }

        $adaptorModel = new AdaptorModel();
        $header = [
            'Marketplace-Type' => 'tmall',
            'Simulation' => CommonTool::loadEnv('omnihub.simution'),
        ];
        if ($interfaceType == 'tradeCreate') {
            $header['Method'] = 'adidas/omnihub/createorder';
        } else if ($interfaceType == 'refundReturnCreate') {
            $header['Method'] = 'eai/baison/returnorderexport';
        } else if ($interfaceType == 'exchangeCreate') {
            $header['Method'] = 'eai/baison/returnorderexport';
        }
        $adaptorModel->setHeader($header);
        $result = $adaptorModel->send($xml);

        $msg = "Result: " . var_export($result, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>Result=");
            print_r($result);
        }

        if ($result['status'] == 'api-success') {
            $return = array('status' => 'api-success', 'message' => 'success', 'data' => array());
            $return['data'] = array('flag' => 'success', 'code' => 1, 'message' => $result['data']);
        } else {
            $return = array('status' => 'api-server-exception', 'message' => $result['message'], 'data' => array());
            $return['data'] = array('flag' => 'success', 'code' => 'api-server-exception', 'message' => $result['message']);
        }
        return $return;
    }

    public function decrypt($extendContent) {
        unset($extendContent['content'], $extendContent['type'], $extendContent['holds']);

        $securityClient = $this->getSecurityClient();
        $fieldMap = $this->getFieldMap();
        $decryptArr = [];
        foreach ((array)$extendContent as $field=>$value) {
            if (empty($value) || is_array($value) || $field == 'holds') {
                //为空的字段不处理
                continue;
//                unset($extendContent[$field]);
            }
            //地址信息保留下来
            if ($field == 'receiver_address') {
                $decryptArr[$field] = $value;
                continue;
            }
            //解密
            //不是加密字段的直接不处理
            if (!isset($fieldMap[$field]) || empty($fieldMap[$field])) {
                continue;
//                unset($extendContent[$field]);
            }

            //公共解密不需要session
            $decryptArr[$field] = $securityClient->decrypt($value, $fieldMap[$field], '');
        }
        return $decryptArr;
    }

    protected function generateSign($params)
    {
        $app_secret = CommonTool::loadEnv('qimen.app_secret');
        ksort($params);

        $stringToBeSigned = $app_secret;
        foreach ($params as $k => $v)
        {
            if(!is_array($v) && "@" != substr($v, 0, 1))
            {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= file_get_contents('php://input');
        $stringToBeSigned .= $app_secret;

        return strtoupper(md5($stringToBeSigned));
    }

    protected function getSecurityClient()
    {
        $app_key = CommonTool::loadEnv('tmall.app_key');
        $app_secret = CommonTool::loadEnv('tmall.app_secret');

        $topClient = new \TopClient($app_key, $app_secret);
        $topClient->gatewayUrl = $this->gatewayUrl;

        $client = new \SecurityClient($topClient, CommonTool::loadEnv('tmall.random_number'));

        return $client;
    }

    protected function getFieldMap()
    {
        //字段名称对应的加解密的类型
        $valid_type = array(
            'user_name' => 'nick',                       // 会员昵称
            'buyer_nick' => 'nick',                      // 买家昵称
            'nick_name' => 'nick',                       // 会员昵称
            'return_user_name' => 'nick',               // 会员昵称
            'buyer_alipay_no' => 'simple',              // 买家支付宝账号
            'receiver_name' => 'receiver_name',        // 收货人的姓名
            'hh_receiver_name' => 'receiver_name',     // 换货人的姓名
            'receiver_mobile' => 'phone',               // 收货人的手机号码
            'hh_receiver_mobile' => 'phone',            // 换货人的手机号码
            'mobile' => 'phone',                         // 收货人的手机号码
            'mobile_phone' => 'phone',                  // 手机号码
            'receiver_mobile_phone' => 'phone',        // 收货人的手机号码
            'receiver_phone' => 'simple',               // 收货人的电话号码
            'receiver_tel' => 'simple',                 // 收货人的电话号码
            'hh_receiver_tel' => 'simple',              // 换货人的电话号码
            'home_phone' => 'simple',                   // 家庭电话
            'buyer_email' => 'simple',                  // 买家邮件地址
            'receiver_email' => 'simple',               // 买家邮件地址
            'email' => 'simple',                         // 买家邮件地址
        );

        return $valid_type;
    }
}