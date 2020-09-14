<?php

namespace gateway\boot;

/**
 * Class CommonTool
 * 核心工具类，使用方法
 * \gateway\boot\CommonTool::Method name
 *
 * @package gateway\boot
 * @author xy.wu
 * @since 2020/3/27 14:05
 */
class CommonTool
{
    /**
     * Method curl
     * 发送http请求
     *
     * @param $url
     * @param $data json or formdata
     * @param array $header
     * @param array $params
     * @return array
     * @author xy.wu
     * @since 2020/3/27 14:05
     */
    public static function curl($url, $data, $header = array(), $params = array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!isset($params['ssl']) || empty($params['ssl'])) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        //php7.3.0 bearer token 不能使用header传输
        if (PHP_VERSION_ID >= 70300 && isset($params['bearer']) && !empty($params['bearer'])) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BEARER);
            curl_setopt($ch, CURLOPT_XOAUTH2_BEARER, $params['bearer']);
        } else if (isset($params['bearer']) && !empty($params['bearer'])) {
            $header[] = "Authorization: Bearer {$params['bearer']}";;
        }
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        $stime = self::getMillisecond();
        $result = curl_exec($ch);
        $etime = self::getMillisecond();
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        $msg = date('Y-m-d H:i:s') . "\tTime:" . var_export(($etime - $stime), true) . "\tUrl:" . var_export($url, true) .
            "\thttpCode:" . var_export($httpCode, true) . "\tUrl:" . var_export($url, true) . "\tData:" . var_export('', true) . "\r\n";

        //记录日志
        self::errorLog($msg, 'api_log');

        if (curl_errno($ch)) {
            $msg = date('Y-m-d H:i:s') . "\tTime:" . var_export(($etime - $stime), true) . "\tUrl:" . var_export($url, true)
                . "\tErrno:" . var_export(curl_errno($ch), true) . "\tError:" . var_export(curl_error($ch), true) . "\r\n";
            //记录日志
            self::errorLog($msg, 'curl_error');
            $return = array('status' => -1, 'message' => '', 'data' => curl_error($ch));
        } else {
            $return = array('status' => 1, 'message' => '', 'data' => $result);
        }
        $return['code'] = $httpCode;
        return $return;
    }

    /**
     * Method getMillisecond
     * 获取毫秒
     *
     * @return float
     * @author xy.wu
     * @since 2020/3/27 14:05
     */
    public static function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * Method errorLog
     * 记录日志
     *
     * @param $msg
     * @param string $fileName
     * @param bool $split
     * @param string $subdir
     * @return bool|void
     * @author xy.wu
     * @since 2020/3/27 14:30
     */
    public static function errorLog($msg, $fileName = 'error_log', $subdir = '') {
        if (empty($msg)) {
            return;
        }
        if (!is_string($msg)) {
            $msg = var_export($msg,true);
        }

        if(!empty($subdir) && substr($subdir,-1) != DIRECTORY_SEPARATOR) {
            $subdir.= DIRECTORY_SEPARATOR;
        }

        $logPath = ROOT_PATH . "runtime/logs" . DIRECTORY_SEPARATOR . $subdir;
        if (!file_exists($logPath) && !mkdir($logPath, 0777, true)) {
            //如果创建目录不成功,直接返回
            return false;
        }
        if (substr($logPath,-1) != DIRECTORY_SEPARATOR) {
            $logPath .= DIRECTORY_SEPARATOR;
        }

        $logFile = $logPath . $fileName;
        $logFile .= "_" . date('Y-m-d',time());

        $msg = self::getTraceFile() . "\t" . $msg;
        $msg = date('Y-m-d H:i:s') . "\t" . GB()->requestId . "\t" . $msg . "\r\n";
        error_log($msg, 3,$logFile . '.log');

        return true;
    }

    /**
     * Method debugLog
     * 开启debug模式之后记录日志
     *
     * @param $msg
     * @param string $fileName
     * @param bool $split
     * @param string $subdir
     * @return bool|void
     * @author xy.wu
     * @since 2020/3/28 12:33
     */
    public static function debugLog($msg, $fileName = 'error_log', $subdir = '') {
        //如果连接中传入了参数，则输出debug日志
        if (isset(GB()->request['__show*debug__']) && GB()->request['__show*debug__'] == 1) {
            return self::errorLog($msg, $fileName, $subdir);
        }
        if (CommonTool::loadEnv('debug_mode') != 1) {
            return;
        }
        return self::errorLog($msg, $fileName, $subdir);
    }

    /**
     * Method loadEnv
     * 加载env环境变量，环境变量未设置的，从config文件中获取
     *
     * @param string $key
     * @return array|false|mixed|string|null
     * @author xy.wu
     * @since 2020/3/28 11:00
     */
    public static function loadEnv($key = '') {
        //不指定直接获取系统配置
        if (!isset($key) || empty($key)) {
            return self::loadConfig($key);
        }

        $envKey = str_replace('.', '_', strtoupper($key));
        $env = getenv($envKey);
        if ($env !== false) {
            return $env;
        }

        return self::loadConfig(strtolower($key));
    }

    /**
     * Method loadConfig
     * 获取系统配置
     *
     * @param string $key
     * @return array|mixed|null
     * @author xy.wu
     * @since 2020/3/27 14:46
     */
    public static function loadConfig($key = '') {
        static $config = array();
        if (empty($config)) {
            $config = include ROOT_PATH . '/config/default.php';
        }

        //获取全部直接返回
        if (!isset($key) || empty($key)) {
            return $config;
        }

        //获取某个键值
        if (stripos($key, '.') !== false) {
            $keyMap = explode('.', $key);
        } else if (isset($key) && !empty($key)) {
            $keyMap = array($key);
        }

        //根据key获取键值
        $return = $config;
        foreach ((array)$keyMap as $configKey) {
            if (isset($return[$configKey])) {
                $return = $return[$configKey];
            } else {
                $return = NULL;
            }
        }

        return $return;
    }

    /**
     * Method loadCache
     * 初始化系统cache类
     *
     * @param string $cacheType
     * @return mixed
     * @author xy.wu
     * @since 2020/3/27 17:15
     */
    public static function loadCache($cacheType = '') {
        //Doctrine\Common\Cache\FilesystemCache
        if (empty($cacheType)) {
            $cacheType = self::loadEnv('cache_type');
        }
        if (empty($cacheType)) {
            $cacheType = 'file';
        }
        static $staticCache = array();
        if (!isset($staticCache[$cacheType])) {
            $cachePath = ROOT_PATH . 'runtime/cache/data';
            if ($cacheType == 'file') {
                $staticCache[$cacheType] = new \Doctrine\Common\Cache\FilesystemCache($cachePath, '.gateway.cache.data');
            } else if ($cacheType == 'php') {
                $staticCache[$cacheType] = new \Doctrine\Common\Cache\PhpFileCache($cachePath, '.gateway.cache.php');
            } else {
                $staticCache[$cacheType] = new \Doctrine\Common\Cache\FilesystemCache($cachePath, '.gateway.cache.data');
            }
        }
        return $staticCache[$cacheType];
    }

    /**
     * Method getRequestId
     * 生成requestid
     *
     * @return string
     * @author xy.wu
     * @since 2020/3/27 14:25
     */
    public static function generateRequestId() {
        // 使用session_create_id()方法创建前缀
        $prefix = session_create_id(date('YmdHis'));
        // 使用uniqid()方法创建唯一id
        $request_id = strtoupper(md5(uniqid($prefix, true)));
        // 格式化请求id
        return self::formatRequestId($request_id);
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    public static function contains($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * 字符串包含有全角字符
     * @param $str
     * @return false|int
     *
     * @author linqihai
     * @since 2020/2/17 14:30
     */
    public static function containSbc($str)
    {
        // @todo 中文符号会被识别
        return preg_match('/[\x{3000}\x{ff01}-\x{ff5f}]/u', $str);
    }

    /**
     * Method getip
     * 获取ip
     *
     * @return array|false|mixed|string
     * @author xy.wu
     * @since 2020/7/7 16:45
     */
    public static function getip() {
        static $realip;
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            } else if (getenv('HTTP_CLIENT_IP')) {
                $realip = getenv('HTTP_CLIENT_IP');
            } else {
                $realip = getenv('REMOTE_ADDR');
            }
        }
        return $realip;
    }

    /**
     * Method formatRequestId
     * 格式化requestid
     *
     * @param $request_id
     * @param string $format
     * @return string
     * @author xy.wu
     * @since 2020/3/27 14:25
     */
    private static function formatRequestId($request_id, $format = '8,4,4,4,12') {
        $tmp = array();
        $offset = 0;
        $cut = explode(',', $format);
        // 根据设定格式化
        if($cut){
            foreach($cut as $v){
                $tmp[] = substr($request_id, $offset, $v);
                $offset += $v;
            }
        }
        // 加入剩余部分
        if($offset<strlen($request_id)){
            $tmp[] = substr($request_id, $offset);
        }
        return implode('-', $tmp);
    }

    /**
     * Method getTraceFile
     * 获取文件trace
     *
     * @return mixed|string
     * @author xy.wu
     * @since 2020/3/28 12:18
     */
    private static function getTraceFile() {
        $backtrace = debug_backtrace();
        $ingnoreFiles = array('CommonTool.php');
        foreach ($backtrace as $key=>$value) {
            if (in_array(basename($value['file']), $ingnoreFiles)) {
                continue;
            }
            if (isset($backtrace[$key + 1])) {
                $trace = "({$value['file']}:{$value['line']}:" . $backtrace[$key + 1]['function'] . ")";
            }
            break;
        }
        $trace = str_replace(ROOT_PATH, '', $trace);
        return $trace;
    }


}