<?php
/*
* File name
* 
* @author xy.wu
* @since 2020/3/27 14:37
*/

namespace gateway\app\gateway\models;

use Doctrine\Common\Cache\FilesystemCache;
use gateway\app\CommonModel;
use gateway\boot\CommonTool;

/**
 * Class OmnihubModel
 * 提供给omnihub请求的地址
 *
 * @package gateway\app\gateway\models
 * @author xy.wu
 * @since 2020/3/27 15:01
 */
class OmnihubModel extends CommonModel {

    /**
     * Method execute
     * 执行转发请求
     *
     * @param array $request
     * @return array
     * @author xy.wu
     * @since 2020/3/27 15:06
     */
    public function execute($request = array()) {

        $header = getallheaders();
        $this->header = &$header;
        $this->request = &$request;

        $logFileName = "omnihub";
        $msg = "Request: " . var_export($request, true) . "\tHeader: " . var_export($header, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($request['__show*debug__']) && $request['__show*debug__'] == 1) {
            print_r("<hr/>Request=");
            print_r($request);
            print_r("<hr/>Header=");
            print_r($header);
        }

        //兼容maketplace_type
        if (isset($header['Marketplace-Type']) && !empty($header['Marketplace-Type'])) {

        } else if (isset($header['maketplace_type']) && !empty($header['maketplace_type'])) {
            $header['Marketplace-Type'] = strtolower(trim($header['maketplace_type']));
        } else if (isset($header['Maketplace-Type']) && !empty($header['Maketplace-Type'])) {
            $header['Marketplace-Type'] = strtolower(trim($header['Maketplace-Type']));
        } else if (isset($header['Maketplace_Type']) && !empty($header['Maketplace_Type'])) {
            $header['Marketplace-Type'] = strtolower(trim($header['Maketplace-Type']));
        } else {
            $header['Marketplace-Type'] = '';
        }
        //兼容maketplace_type
        if (isset($header['Simulation']) && !empty($header['Simulation'])) {

        } else if (isset($header['simulation']) && !empty($header['simulation'])) {
            $header['Simulation'] = strtolower(trim($header['simulation']));
        } else {
            $header['Simulation'] = 0;
        }

        $maketplaceType = strtolower(trim($header['Marketplace-Type']));
        if (empty($maketplaceType)) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Missing Required Header Marketplace-Type', 'data' => array());
            return $return;
        }
        /*if (!isset($header['app_key']) || empty($header['app_key'])) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Missing Required Parameters app_key', 'data' => array());
            return $return;
        }*/
        /*if (!isset($header['co']) || empty($header['co'])) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Missing Required Parameters co', 'data' => array());
            return $return;
        }*/
        /*if (!isset($header['sign']) || empty($header['sign'])) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Missing Required Parameters sign', 'data' => array());
            return $return;
        }*/
        if (!in_array($maketplaceType, array('tmall', 'jd'))) {
            $return = array('status' => 'api-invalid-parameter', 'message' => 'Not Allowed Marketplace-Type[' . trim($header['Marketplace-Type']) . '] Calls', 'data' => array());
            return $return;
        }

        //获取xml格式数据
        if (isset($request['data']) && !empty(isset($request['data']))) {
            $content = $request['data'];
        } else {
            $content = file_get_contents('php://input');
        }

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
     * 发送请求到adaptor
     *
     * @param $content
     * @return false|mixed|string
     * @author xy.wu
     * @since 2020/4/3 21:19
     */
    public function send(&$content)
    {
        GB()->setState('adaptorapi');
        $logFileName = 'omnihub';
        $maketplaceType = trim($this->header['Marketplace-Type']);

        //通过环境变量获取配置，获取不到再从配置文件获取
        $this->appToken['url'] = CommonTool::loadEnv("{$maketplaceType}.url");
        $this->appToken['client_id'] = CommonTool::loadEnv("{$maketplaceType}.client_id");
        $this->appToken['client_secret'] = CommonTool::loadEnv("{$maketplaceType}.client_secret");
        $this->appToken['access_token'] = CommonTool::loadEnv("{$maketplaceType}.access_token");
        if (substr($this->appToken['url'],-1) != '/') {
            $this->appToken['url'] .= '/';
        }

        //获取accesstoken
        $accessToken = $this->getAdaptorAccessToken();

        //header
        $post_header = array(
            "Customer:adidas",
            "Marketplace-Type:{$maketplaceType}",
            "maketplace-type:{$maketplaceType}",
            "maketplace_type:{$maketplaceType}",
            "Accept:application/json",
            "Simulation:" . (isset($this->header['Simulation']) && $this->header['Simulation'] == 1 ? 1 : CommonTool::loadEnv("{$maketplaceType}.simulation")),
            "Source:BaisonHub Hostname " . gethostname(),
        );
        $url = $this->appToken['url'] . 'api';
        $post_params['bearer'] = $accessToken;

        $msg = "Post Url: " . $url . "\tPost Header: " . var_export($post_header, true) . "\tPost Params: " . var_export($post_params, true) . "\tPost Data: " . var_export($content, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($this->request['__show*debug__']) && $this->request['__show*debug__'] == 1) {
            print_r("<hr/>Post Url=");
            print_r($url);
            print_r("<hr/>Post Header=");
            print_r($post_header);
            print_r("<hr/>Post Data=");
            print_r($content);
        }

        //发送请求
        $result = CommonTool::curl($url, $content, $post_header, $post_params);

        $msg = "Post Url: " . $url . "\tResponse: " . var_export($result, true);
        CommonTool::debugLog($msg, $logFileName);
        if (isset($this->request['__show*debug__']) && $this->request['__show*debug__'] == 1) {
            print_r("<hr/>Response=");
            print_r($result);
        }

        if ((!isset($this->request['retry']) || $this->request['retry'] != 1) && $result['status'] == 1 && isset($result['data']) && !empty($result['data'])) {
            $json = json_decode($result['data'], true);
            if ($json['status'] == 'api-invalid-parameter' && $json['code'] == 401 && stripos($json['message'], 'authentication-exception') !== false) {
                //需要强制刷新access_token
                $this->getAdaptorAccessToken(array(), 1);
                $this->request['retry'] = 1;
                return $this->send($content);
            }/* else if ($json['status'] == 'api-invalid-parameter' && $json['code'] == 400 && stripos($json['message'], 'Missing session:') !== false) {
                //需要强制刷新access_token
                $this->getAdaptorAccessToken(array(), 1);
                $request['retry'] = 1;
                return $this->execute($request);
            }*/
        }

        $httpCode = $result['code'] ?? 500;

        if ($result['status'] == 1) {
            $return = array('status' => 'api-success', 'message' => 'Success', 'data' => $result['data']);
        } else {
            $return = array('status' => 'api-server-exception', 'code' => 500, 'message' => $result['message'], 'data' => $result['data']);
        }
        $return['code'] = $httpCode;

        return $return;
    }
}