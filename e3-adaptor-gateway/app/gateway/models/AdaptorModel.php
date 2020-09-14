<?php
/*
* File name
* 
* @author xy.wu
* @since 2020/3/27 14:37
*/

namespace gateway\app\gateway\models;

use gateway\app\CommonModel;
use gateway\boot\CommonTool;

/**
 * Class AdaptorModel
 * 提供给adaptor请求的地址
 *
 * @package gateway\app\gateway\models
 * @author xy.wu
 * @since 2020/3/27 15:01
 */
class AdaptorModel extends CommonModel
{
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
        $header = getallheaders();
        $this->header = &$header;
        $this->request = &$request;

        //记录日志
        $logFileName = 'adaptor';
        $msg = "Request: " . var_export($request, true) . "\tHeader: " . var_export($header, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>Request=");
            print_r($request);
            print_r("<hr/>Header=");
            print_r($header);
        }

        //omnihub进入的转到omnihub处理
        //进入aisino处理
        if ((isset($header['Source']) && strtolower($header['Source']) == 'aisino') ||
            (isset($header['Method']) && strtolower($header['Method']) == 'aisino.sh.fpkj') ||
            (isset($header['Method']) && strtolower($header['Method']) == 'aisino.sh.push.invoice')) {
            $aisinoModel = new AisinoModel();
            return $aisinoModel->execute($request);
        } else if (!isset($header['Source']) || strtolower($header['Source']) != 'adaptor') {
            $omnihubModel = new OmnihubModel();
            return $omnihubModel->execute($request);
        }

        if (!isset($header['Marketplace-Type']) || empty($header['Marketplace-Type'])) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Missing Required Header Marketplace-Type', 'data' => array());
            return $return;
        }
        /*if (!isset($header['app_key']) || empty($header['app_key'])) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Missing Required Parameters app_key', 'data' => $header);
            return $return;
        }*/
        /*if (!isset($header['co']) || empty($header['co'])) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Missing Required Parameters co', 'data' => $header);
            return $return;
        }*/
        /*if (!isset($header['sign']) || empty($header['sign'])) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Missing Required Parameters sign', 'data' => $header);
            return $return;
        }*/
        $maketplaceType = strtolower(trim($header['Marketplace-Type']));
        if (!in_array($maketplaceType, array('tmall', 'jd'))) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Not Allowed Marketplace-Type[' . trim($header['Marketplace-Type']) . '] Calls', 'data' => array());
            return $return;
        }

        if (!isset($header['Method']) || empty($header['Method'])) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Missing Required Header Method', 'data' => array());
            return $return;
        }

        //获取xml格式数据
        if (isset($request['data']) && !empty(isset($request['data']))) {
            $content = $request['data'];
        } else {
            $content = file_get_contents('php://input');
        }

        //校验签名
        if ($this->checkAdaptorAuthorization($content) === false) {
            $return = array('status' => 'api-invalid-parameter-authentication', 'message' => 'Authorization Error', 'data' => array());
            return $return;
        }

        //实际请求发送
        $return = $this->send($content);

        $msg = __METHOD__ . " Response: " . var_export($return, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>Response=");
            print_r($return);
        }

        return $return;
    }

    /**
     * Method send
     * 发送请求到omnihub
     *
     * @param $content
     * @return false|mixed|string
     * @author xy.wu
     * @since 2020/4/3 21:19
     */
    public function send(&$content)
    {
        GB()->setState('omnihubapi');
        $logFileName = 'adaptor';

        //通过环境变量获取配置，获取不到再从配置文件获取
        $this->appToken['url'] = CommonTool::loadEnv("omnihub.url");
        if (substr($this->appToken['url'], -1) != '/') {
            $this->appToken['url'] .= '/';
        }

        //header
        $post_header = array(
            "Content-Type:application/xml",
            "Source:BaisonHub Hostname " . gethostname(),
            'Authorization: APPCODE ' . CommonTool::loadEnv('omnihub.appcode'),
        );
        $url = $this->appToken['url'] . $this->header['Method'];

        if ((isset($this->header['Simulation']) && $this->header['Simulation'] == 1) || (CommonTool::loadEnv("omnihub.simulation") == 1)) {

            //毫秒
            $usleep_time = mt_rand(100, 300);
            usleep($usleep_time * 1000);

            $return_msg_arr = array();
            //成功消息
            $return_msg_arr[] = "<OrderCreateResp><Message><Code>SUCCESS</Code><Description>Order Create Request is processed successfully</Description></Message></OrderCreateResp>";
            //失败消息
            $return_msg_arr[] = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><SOAP-ENV:Body><SOAP-ENV:Fault><faultcode>FAILURE</faultcode><faultstring>An error occured during processing</faultstring><faultactor>/adidas/omnihub/ordercancellation</faultactor><detail><text>Order Cancellation Request Failed to process successfully. Please check the request message and retry.</text></detail></SOAP-ENV:Fault></SOAP-ENV:Body></SOAP-ENV:Envelope>';
            //失败消息2
            $return_msg_arr[] = "<?xml version='1.0' encoding='UTF-8'?>" .
                '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><SOAP-ENV:Body><SOAP-ENV:Fault><faultcode>SOAP-ENV:Server</faultcode><faultstring>A timeout occurred during processing</faultstring><faultactor>/adidas/omnihub/createorder</faultactor><detail><text>Timeout. Broker INADCNI1 did not provide a response within the specified time interval (60seconds)</text></detail></SOAP-ENV:Fault></SOAP-ENV:Body></SOAP-ENV:Envelope>';
            //失败消息3
            $return_msg_arr[] = "<h1>Gateway Timeout</h1>";

            /*$random_num = mt_rand(1, 100);
            if ($random_num <= 10) {
                $return = $return_msg_arr[1];
            } else if ($random_num >= 90) {
                $return = $return_msg_arr[2];
            } else if ($random_num >= 80) {
                $return = $return_msg_arr[3];
            } else {
                $return = $return_msg_arr[0];
            }*/
            $return = $return_msg_arr[0];
            $return = array('status' => 'api-success', 'code' => 200, 'message' => 'Success', 'data' => $return);
        } else {
            //发送请求
            $msg = "Post Url: " . $url . "\tPost Header: " . var_export($post_header, true) . "\tPost Data: " . var_export($content, true);
            CommonTool::debugLog($msg, $logFileName);
            if (isset($this->request['__show*debug__']) && $this->request['__show*debug__'] == 1) {
                print_r("<hr/>Post Url=");
                print_r($url);
                print_r("<hr/>Post Header=");
                print_r($post_header);
                print_r("<hr/>Post Data=");
                print_r($content);
            }

            $result = CommonTool::curl($url, trim($content), $post_header, true);

            $msg = "Post Url: " . $url . "\tResponse: " . var_export($result, true);
            CommonTool::debugLog($msg, $logFileName);
            if (isset($this->request['__show*debug__']) && $this->request['__show*debug__'] == 1) {
                print_r("<hr/>Response=");
                print_r($result);
            }

            $httpCode = $result['code'] ?? 500;

            if ($result['status'] == 1) {
                $return = array('status' => 'api-success', 'message' => 'Success', 'data' => $result['data']);
            } else {
                $return = array('status' => 'api-server-exception', 'message' => $result['message'], 'data' => $result['data']);
            }
            $return['code'] = $httpCode;
        }

        return $return;
    }
}