<?php
/*
 * File name
 * 
 * @author xy.wu
 * @since 2020/3/30 15:27
 */

namespace gateway\app\gateway\models\security;

use Exception;
use gateway\boot\CommonTool;

if (!defined("TOP_SDK_WORK_DIR")) {
    define('TOP_SDK_WORK_DIR', ROOT_PATH . 'runtime/logs/taobao/');
}
require_once ROOT_PATH . 'library/taobao_sdk/TopSdk.php';

class TaobaoSecurityModel {

    /**
     * 淘宝接口地址
     *
     * @var string
     */
    private static $gatewayUrl = "https://eco.taobao.com/router/rest";

    /**
     * 淘宝app id，先从env中获取，然后从配置文件获取
     *
     * @var null
     */
    private static $appId = null;

    /**
     * 淘宝app secret，先从env中获取，然后从配置文件获取
     *
     * @var null
     */
    private static $appSecret = null;

    /**
     * 淘宝app random，先从env中获取，然后从配置文件获取
     * 对应关系random_number.conf.php
     * '23036663' => 'q3pY1umb8gTE/mCdWyWj//6fiVxX5AbNCw9SheF145Y=',   //百胜E3_EC电子商务管理软件新key
     *
     * @var null
     */
    private static $appRandom = null;

    /**
     * Method encrypt
     * 对传入的数据进行加密操作
     *
     * @param $data
     * @return array
     * @author xy.wu
     * @since 2020/3/30 16:31
     */
    public static function encrypt($data)
    {
        if (empty($data) || !is_array($data)) {
            // 空串直接返回
            return $data;
        }

        //环境变量是否关闭加解密
        if (CommonTool::loadEnv("TURN_OFF_ENCRYPTION") == 1) {
            // 不加密的场景、直接返回
            return $data;
        }

        //环境变量获取天猫的app_id和app_secret
        self::$appId = CommonTool::loadEnv('ENCRYPTION_TMALL_APP_ID');
        self::$appSecret = CommonTool::loadEnv('ENCRYPTION_TMALL_APP_SECRET');
        self::$appRandom = CommonTool::loadEnv('ENCRYPTION_TMALL_APP_RANDOM');

        if (empty(self::$appId) || empty(self::$appSecret) || empty(self::$appRandom)) {
            return $data;
        }

        //加解密客户端
        $securityClient = self::getSecurityClient();
        //加解密所有字段的值
        $securityField = self::getEncryptedField();
        foreach ((array)$data as $key=>$value) {
            if (!isset($securityField[$key]) || empty($securityField[$key])) {
                continue;
            }
            if ($securityClient->isEncryptData($data, $securityField[$key])) {
                // 如果已经加密，则不再进行加密
                continue;
            }

            try {
                //加密
                $data[$key] = $securityClient->encrypt($value, $securityField[$key]);
            } catch (Exception $e) {
                CommonTool::errorLog("Encrypt Field[{$key}]:[{$value}] Failed. Exception:" . $e->getMessage(), 'security', 'taobao');
                continue;
            }
        }
        return $data;
    }

    /**
     * Method decrypt
     * 对请求的数组进行解密操作
     *
     * @param $data
     * @return array
     * @author xy.wu
     * @since 2020/3/30 16:31
     */
    public static function decrypt($data)
    {
        if (empty($data) || !is_array($data)) {
            // 空串直接返回
            return $data;
        }

        //加解密客户端
        $securityClient = self::getSecurityClient();
        //加解密所有字段的值
        $securityField = self::getEncryptedField();
        foreach ((array)$data as $key=>$value) {
            if (!isset($securityField[$key]) || empty($securityField[$key])) {
                continue;
            }
            if (mb_strlen(trim($data)) < 2) {
                continue;
            }
            if (!$securityClient->isEncryptData($data, $securityField[$key])) {
                // 如果没有加密，不进行解密，则不再进行解密
                continue;
            }

            try {
                //加密
                $data[$key] = $securityClient->decrypt($value, $securityField[$key], null);
            } catch (Exception $e) {
                CommonTool::errorLog("Decrypt Field[{$key}]:[{$value}] Failed. Exception:" . $e->getMessage(), 'security', 'taobao');
                continue;
            }
        }
        return $data;
    }

    /**
     * Method getSecurityClient
     * 获取security客户端
     *
     * @return mixed
     * @author xy.wu
     * @since 2020/3/30 16:14
     */
    private static function getSecurityClient()
    {
        static $securityClient = null;
        if (is_null($securityClient)) {
            $topClient = new \TopClient;
            $topClient->appkey = self::$appId;
            $topClient->secretKey = self::$appSecret;
            $topClient->gatewayUrl = self::$gatewayUrl;

            $securityClient = new \SecurityClient($topClient, self::$appRandom);

            // 设置缓存处理器
            self::$securityClient->setCacheClient(new TaobaoSecurityCache());
        }

        return self::$securityClient;
    }

    /**
     * Method getEncryptedField
     * 获取加密字段对应的加密类型
     *
     * @return array
     * @author xy.wu
     * @since 2020/3/30 16:22
     */
    private static function getEncryptedField()
    {
        // 报文中字段对应的加解密的类型
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