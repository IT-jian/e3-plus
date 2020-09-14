<?php
/*
* File name
* 
* @author xy.wu
* @since 2020/3/27 14:39
*/

namespace gateway\app;

use gateway\boot\CommonTool;

class CommonModel {

    /**
     * 获取token需要的地址以及key secret信息
     *
     * @var array
     */
    protected $appToken = array();

    /**
     * 保存请求的头信息
     *
     * @var array
     */
    protected $header = array();

    /**
     * 保存请求的参数信息
     *
     * @var array
     */
    protected $request = array();

    /**
     * 默认日志文件名称
     *
     * @var string
     */
    protected $logFileName = 'error_log';

    /**
     * Method setHeader
     * 设置header信息
     *
     * @param $header
     * @author xy.wu
     * @since 2020/4/3 21:17
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * Method setRequest
     * 设置request信息
     *
     * @param $request
     * @author xy.wu
     * @since 2020/4/3 21:17
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * Method Authorization
     * 验证签名
     *
     * @param $data
     * @return bool
     * @author xy.wu
     * @since 2020/3/28 11:36
     */
    public function checkAdaptorAuthorization($data) {
        //如果开启跳过签名，则直接跳过签名验证
        if (CommonTool::loadEnv("{$this->header['Marketplace-Type']}.skip_sign_verify") == 1) {
            return true;
        }

        //如果没有Authorization头直接失败
        if (!isset($this->header['Authorization']) || empty($this->header['Authorization'])) {
            CommonTool::errorLog("Missing Required Header Authorization");
            return false;
        }
        //签名规则  md5(client_id . serialize($data)

        $clientId = CommonTool::loadEnv("{$this->header['Marketplace-Type']}.client_id");
        $clientSceret = CommonTool::loadEnv("{$this->header['Marketplace-Type']}.client_secret");

        $sign = md5($clientId . md5($clientId . serialize($data) . $clientSceret) . md5($clientId . $clientSceret));
        if ($sign != $this->header['Authorization']) {
            CommonTool::errorLog("Authorization Error: Header Authorization is [{$this->header['Authorization']}], But Calculated By Adaptor is [{$sign}]");
            return false;
        }
        return true;
    }

    /**
     * Method getAccessToken
     * 获取access_token
     *
     * @param array $params
     * @param boolean $force 1:强制清除重新获取
     * @return false|mixed|string
     * @author xy.wu
     * @since 2020/3/27 16:31
     */
    public function getAdaptorAccessToken($params = array(), $force = 0)
    {
        if (empty($params)) {
            $params = $this->appToken;
        }

        //@todo 直接硬编码token
        if (isset($params['access_token']) && !empty($params['access_token'])) {
            return $params['access_token'];
        }
        //@todo 直接硬编码token

        $cacheKey = __FUNCTION__ . md5(serialize($params));
        $cache = CommonTool::loadCache();

        //强制删除缓存token
        if ($force) {
            $cache->delete($cacheKey);
        }
        if ($cache->contains($cacheKey)) {
            $accessToken = $cache->fetch($cacheKey);
        } else {
            //获取token
            if (substr($params['url'],-1) != '/') {
                $params['url'] .= '/';
            }
            $tokenUrl = $params['url'] . 'api_token';

            unset($params['url']);
            CommonTool::errorLog("get access token, url:" . $tokenUrl);
            $result = CommonTool::curl($tokenUrl, $params);
            CommonTool::errorLog("get access token, result:" . var_export($result ,true));
            if ($result['status'] == 1 && !empty($result['data'])) {
                $tokenData = json_decode($result['data'], true);
                if ($tokenData['status'] == 'api-success' && $tokenData['code'] == 200 && isset($tokenData['data']['access_token']) && !empty($tokenData['data']['access_token'])) {
                    $accessToken = $tokenData['data']['access_token'];
                    //提前一天过期
                    $expiresIn = $tokenData['data']['expires_in'] - 86400;
                    $cache->save($cacheKey, $accessToken, $expiresIn);
                }
            } else {
                $accessToken = '';
            }
        }
        return $accessToken;
    }

    /**
     * Method deal_decrypt
     * 解密处理
     *
     * @param $extendContent
     * @return mixed
     * @author xy.wu
     * @since 2020/7/4 22:01
     */
    public function deal_decrypt($extendContent) {

        //调用子类的方法
        $decryptArr = $this->decrypt($extendContent);

        $msg = "ExtendContent: " . var_export($extendContent, true) . "\tDecryptArr: " . var_export($decryptArr, true);
        CommonTool::debugLog($msg, $this->logFileName);
        if (isset($this->request['__show*debug__']) && $this->request['__show*debug__'] == 1) {
            print_r("<hr/>ExtendContent=");
            print_r($extendContent);
            print_r("<hr/>DecryptArr=");
            print_r($decryptArr);
        }

        $xml = $extendContent['content'];

        //特殊字符处理；全角字符处理；收货地址长度处理；参数
        $params = $decryptArr;

        //只有订单需要此处理，退单、换货单不需要此处理
        if ($extendContent['type'] == 'tradeCreate' && !empty($decryptArr) && isset($decryptArr['receiver_address']) && !empty($decryptArr['receiver_address'])) {
            //********************************收货地址长度处理********************************//
            $addressLineSplitLength = 50;
            $addressLineSplitCount = ceil(mb_strlen($decryptArr['receiver_address'], 'UTF-8') / $addressLineSplitLength);
            $addressLineSplitCount = min($addressLineSplitCount, 4);
            for ($i = 1; $i <= $addressLineSplitCount; $i++) {
                ${"addressLine{$i}"} = mb_substr($decryptArr['receiver_address'], ($i - 1) * $addressLineSplitLength, $addressLineSplitLength, 'UTF-8');
            }
            for ($i = 2; $i <= $addressLineSplitCount; $i++) {
                $xml = str_replace('AddressLine' . $i . '=""', 'AddressLine' . $i . '="' . ${"addressLine{$i}"} . '"', $xml);
            }
            //********************************收货地址长度处理********************************//

            $decryptArr['receiver_address'] = $addressLine1;
        }

        foreach ((array)$decryptArr as $field=>$value) {
            $xml = str_replace($extendContent[$field], $value, $xml);
        }

        $params['seller_memo'] = $extendContent['holds']['seller_memo'] ?? '';
        $params['buyer_message'] = $extendContent['holds']['buyer_message'] ?? '';
        $this->deal_special_character($xml, $params);

        return $xml;
    }

    /**
     * Method deal_special_character
     * 特殊字符处理；全角字符处理；收货地址长度处理
     *
     * @param $xml
     * @param $decryptArr
     * @return mixed|void
     * @author xy.wu
     * @since 2020/7/4 21:57
     */
    public function deal_special_character(&$xml, $decryptArr) {
        if (empty($xml) || empty($decryptArr)) {
            return;
        }
        $placeholder = '<BaisonHolds/>';
        if (!CommonTool::contains($xml, [$placeholder])) {
            return;
        }
        $orderHoldTypes = '<OrderHoldTypes><OrderHoldType ReasonText="Address Invalid Hold" ResolverUserId="" HoldType="ADDRESS_INVALID_HOLD"/></OrderHoldTypes>';
        //全角字符
        /*$sbcCharCheckFields = ['receiver_address', 'seller_memo', 'buyer_message'];
        $sbcCharCheckFields = [];
        //特殊字符
        $specialCharCheckFields = ['receiver_name', 'receiver_address', 'seller_memo', 'buyer_message'];
        $specialCharCheckFields = [];

        foreach ($sbcCharCheckFields as $field) {
            $value = trim($decryptArr[$field]);
            if (!empty($value) && CommonTool::containSbc($value)) { // 包含全角字符
                $xml = str_replace($placeholder, $orderHoldTypes, $xml);
                $msg = "ContainSbc: Field: " . var_export($field, true) . "\tValue: " . var_export($value, true);
                CommonTool::debugLog($msg, $this->logFileName);
                return;
            }
        }
        foreach ($specialCharCheckFields as $field) {
            $value = trim($decryptArr[$field]);
            if (!empty($value) && CommonTool::contains($value, ['@', '*', '#', '$', '&'])) { // 包含特殊字符
                $xml =  str_replace($placeholder, $orderHoldTypes, $xml);
                $msg = "ContainSpecialChar: Field: " . var_export($field, true) . "\tValue: " . var_export($value, true);
                CommonTool::debugLog($msg, $this->logFileName);
                return;
            }
        }*/
        /*if (mb_strlen($decryptArr['receiver_address'], 'UTF-8') > 200) {
            $xml =  str_replace($placeholder, $orderHoldTypes, $xml);
            return;
        }*/
        $xml =  str_replace($placeholder, '', $xml);
        return;
    }
}