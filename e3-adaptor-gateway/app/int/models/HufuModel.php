<?php
/*
* File name
* 
* @author xy.wu
* @since 2020/3/27 14:37
*/

namespace gateway\app\int\models;

use ACES\SecretJdClient;
use gateway\app\CommonModel;
use gateway\app\gateway\models\AdaptorModel;
use gateway\boot\CommonTool;

if (!defined("LOGCONSOLE")) {
    define('LOGCONSOLE', __DIR__  . "/../../../runtime/logs/tde_" . date('Y-m-d') . ".log");
}
require_once __DIR__ . "/../../../library/jingdong_sdk/vendor/autoload.php";

/**
 * Class HufuModel
 * 提供给虎符调用的地址
 *
 * @package gateway\app\int\models
 * @author xy.wu
 * @since 2020/4/3 22:34
 */
class HufuModel extends CommonModel
{
    /**
     * 生产环境地址
     *
     * @var string
     */
    protected $gatewayUrl = '';

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
        $this->request = $request;
        //记录日志
        $this->logFileName = 'hufu';

        $header = getallheaders();

        $msg = "Request: " . var_export($request, true) . "\tHeader: " . var_export($header, true);
        CommonTool::debugLog($msg, $this->logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>Request=");
            print_r($request);
            print_r("<hr/>Header=");
            print_r($header);
        }

        if (!isset($request['app_key']) || empty($request['app_key']) || $request['app_key'] != CommonTool::loadEnv('hufu.app_key')) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Not Allowed app_key[' . $request['app_key'] . ']', 'data' => array());
            $return['data'] = array('flag' => 'failure', 'code' => 'api-invalid-parameter-app_key', 'message' => $return['message']);
            return $return;
        }

        /*if (!isset($request['method']) || empty($request['method']) || !in_array($request['method'], array('qimen.taobao.pos.weborder.sync', 'qimen.taobao.pos.order.return.add'))) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Not Allowed method[' . $request['method'] . ']', 'data' => array());
            $return['data'] = array('flag' => 'failure', 'code' => 'api-invalid-parameter-method', 'message' => $return['message']);
            return $return;
        }*/

        $params = $request;
        unset($params['sign']);

        $sign = $this->generateSign($params);
        $msg = "Sign: " . var_export($sign, true) . "\tHufuSign: " . var_export($request['sign'], true);
        CommonTool::debugLog($msg, $this->logFileName);
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

        $extendContent = $content = json_decode($request['data'], true);
        $msg = "Content: " . var_export($content, true);
        CommonTool::debugLog($msg, $this->logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>Content=");
            print_r($content);
        }

        $interfaceType = isset($extendContent['type']) ? $extendContent['type'] : '';
        if (!in_array($interfaceType, array('tradeCreate', 'refundReturnCreate', 'exchangeCreate'))) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Not Allowed extend_props_type[' . $extendContent['type'] . ']', 'data' => array());
            $return['data'] = array('flag' => 'failure', 'code' => 'api-invalid-parameter-extend_props_type', 'message' => $return['message']);
            return $return;
        }

        if (!isset($extendContent['content']) || empty($extendContent['content'])) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Missing Required content', 'data' => array());
            $return['data'] = array('flag' => 'failure', 'code' => 'api-invalid-parameter-content', 'message' => $return['message']);
            return $return;
        }

        //解密
        $xml = $this->deal_decrypt($extendContent);

        $msg = "Xml: " . var_export($xml, true);
        CommonTool::debugLog($msg, $this->logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>Xml=");
            print_r($xml);
        }

        $adaptorModel = new AdaptorModel();
        $header = [
            'Marketplace-Type' => 'jd',
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
        CommonTool::debugLog($msg, $this->logFileName);
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
        $start_time = CommonTool::getMillisecond();
        unset($extendContent['content'], $extendContent['type'], $extendContent['holds']);

        $accessToken = $extendContent['session'];
        $app_key = CommonTool::loadEnv('hufu.app_key');
        $app_secret = CommonTool::loadEnv('hufu.app_secret');

        unset($extendContent['session']);
//        unset($extendContent['buyer_nick'], $extendContent['buyer_email']);

        $secretJdClient = SecretJdClient::getInstance($accessToken, $app_key, $app_secret);

        $decryptArr = [];
        foreach ((array)$extendContent as $field=>$value) {
            if (empty($value) || is_array($value) || $field == 'holds') {
                //为空的字段不处理
                continue;
            }
            try {
                //解密
                $decryptArr[$field] = $secretJdClient->decrypt($value);
            } catch (\Exception $e) {
                $msg = "Jd Decrypt:" . var_export($value, true) . "\tError:" . var_export($e->getMessage(), true);
                CommonTool::errorLog($msg);
            }
        }
        $endTime = CommonTool::getMillisecond();
        $msg = "Jingdong decrypt: " . var_export(($endTime - $start_time), true);
        CommonTool::debugLog($msg, 'jingdong_decrypt');

        return $decryptArr;
    }

    protected function generateSign($params)
    {
        $clientId = CommonTool::loadEnv("jd.client_id");
        $clientSceret = CommonTool::loadEnv("jd.client_secret");

        $sign = md5($clientId . md5($clientId . serialize(file_get_contents('php://input')) . $clientSceret) . md5($clientId . $clientSceret));

        return $sign;
    }
}