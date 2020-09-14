<?php

namespace gateway\boot;

class GatewayBoot
{

    /**
     * request请求参数
     *
     * @var array
     */
    public $request = array();

    /**
     * response返回结构
     *
     * @var array
     */
    public $response = array();

    /**
     * app请求的应用信息
     *
     * @var array
     */
    public $app = array();

    /**
     * requestId（唯一请求id）
     *
     * @var string
     */
    public $requestId = null;

    /**
     * 统计执行时间
     *
     * @var array
     */
    private $stateCon = [];

    /**
     * GatewayBoot constructor.
     */
    public function __construct()
    {
        $this->instance();
    }

    /**
     * Method instance
     * 初始化
     *
     * @author xy.wu
     * @since 2020/4/25 12:54
     */
    public function instance()
    {
        $this->stateCon = [
            'global' => [
                'time' => ['start' => CommonTool::getMillisecond()],
                'memory' => ['start' => memory_get_usage()],
            ],
        ];
        //设置时区
        $this->setGlobalSetting();
        //加载env
        $this->loadEnv();
    }

    /**
     * Method setGlobalSetting
     * 设置全局的配置等
     *
     * @author xy.wu
     * @since 2020/4/8 13:50
     */
    public function setGlobalSetting()
    {
        //设置时区
        $timezone = CommonTool::loadEnv('global.timezone');
        if (!empty($timezone) && $timezone != 'Asia/Shanghai') {
            ini_set('date.timezone',$timezone);
        }
    }

    /**
     * Method loadEnv
     * 加载.env环境变量
     *
     * @author xy.wu
     * @since 2020/4/8 11:49
     */
    public function loadEnv()
    {
        // 加载环境变量配置文件
        // is_file 判断给定文件名是否为一个正常的文件 参看手册
        if (is_file(ROOT_PATH . '.env')) {
            # parse_ini_file 解析一个配置文件 并已数组的形式返回 参看手册
            $env = parse_ini_file(ROOT_PATH . '.env', true);
            foreach ($env as $key => $val) {
                $name = strtoupper($key);
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        $item = $name . '_' . strtoupper($k);
                        if (!getenv($item)) {
                            putenv("{$item}={$v}");
                        }
                    }
                } else if (!getenv($name)) {
                    putenv("{$name}={$val}");
                }
            }
        }
    }

    /**
     * Method setDisplayErrorsMode
     * 设置display errors
     *
     * @author xy.wu
     * @since 2020/4/6 14:45
     */
    public function setDisplayErrorsMode()
    {
        if (isset($this->request['__show*debug__']) && $this->request['__show*debug__'] == 1) {
            ini_set('display_errors',1);
            error_reporting(E_ALL);
            return;
        }

        $debug = CommonTool::loadEnv('debug_mode');

        if ($debug == 1) {
            ini_set('display_errors',1);
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors',0);
            error_reporting(E_ALL & ~(E_STRICT|E_NOTICE|E_WARNING));
        }

        return;
    }

    /**
     * 核心run
     */
    public function run() {
        try {
            //获取唯一requestId
            $this->requestId = CommonTool::generateRequestId();

            //处理路由
            $this->parseRouter();
            $this->app['obj'] = new $this->app['class']();

            if (!method_exists($this->app['obj'], $this->app['method'])) {
                throw new \Exception("method:[{$this->app['method']}]请求的方法不存在");
            }

            $this->stateCon['app'] = [
                'time' => ['start' => CommonTool::getMillisecond()],
                'memory' => ['start' => memory_get_usage()],
            ];

            $this->app['obj']->{$this->app['method']}($this->request, $this->response, $this->app);
        } catch (\Error $e) {
            $msg = "Server:" . var_export($_SERVER, true) . "\tRequest:" . var_export($this->request, true) . "\tApp:" . var_export($this->app, true) . "\tError:" . var_export($e, true);
            CommonTool::errorLog($msg);
            $this->response = array('status' => -1, 'code' => 500, 'message' => $e->getMessage());
            http_response_code(500);
        } catch (\Exception $e) {
            $msg = "Server:" . var_export($_SERVER, true) . "\tRequest:" . var_export($this->request, true) . "\tApp:" . var_export($this->app, true) . "\tError:" . var_export($e, true);
            CommonTool::errorLog($msg);
            $this->response = array('status' => -1, 'code' => 500, 'message' => $e->getMessage());
            http_response_code(500);
        }

        $httpCode = http_response_code();
        $msg = "\t\nHttpCode: " . $httpCode
            . "\t\nIP: " . CommonTool::getip()
            . "\t\nREQUEST_URI: " . $this->formatJson($_SERVER['REQUEST_URI'] ?? ($_SERVER['PHP_SELF'] ?? $_SERVER['PATH_INFO'] ?? ''))
            . "\t\nResponse: " . $this->formatJson($this->response)
            . "\t\nRequest: " . $this->formatJson($this->request) . "\t\nHeader: " . $this->formatJson(getallheaders())
            . "\t\nApp: " . $this->formatJson($this->app);
        CommonTool::debugLog($msg, 'gateway');

        $this->parseResponse();
    }

    /**
     * 解析路由信息
     *
     * @throws \Exception
     */
    public function parseRouter() {
        //url地址规则  http://ip:port/{模块名:ftp}/{控制器名:}/{方法名:}/{参数}
        //    参数格式：?p1=v1&p2=v2&p3=&p4=v4
        //    参数格式：/p1/v1/p2/v2/p3//p4/v4
        //    参数格式：?p1/v1/p2/v2/p3//p4/v4

        $uri = isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (stripos($uri, 'index.php') !== false) {
            $uri = substr($uri, (stripos($uri, '/index.php') + 11), strlen($uri));
        } else if (stripos($uri, '/public') !== false) {
            $uri = substr($uri, (stripos($uri, '/public') + 9), strlen($uri));
        } else {
            $uri = substr($uri, 1, strlen($uri));
        }
        if (stripos($uri, '?') !== false) {
            //有问号参数，uri取问号之前的
            $uri = str_replace('/?', '?', $uri);
            $uriExplode = explode('?', $uri);
            $uri = $uriExplode[0];
            $uriParams = $uriExplode[1];
        }

//        $uri = str_replace('/?', '?', $uri);
//        $uri = str_replace('?', '/', $uri);
        if (empty($uri)) {
            $uri = 'index/index/index';
        }
        $params = explode('/', $uri);

        if (count($params) < 1) {
            throw new \Exception("uri:[{$uri}]解析不到必须的模块和控制器");
        }
        $this->app['app'] = !empty($params[0]) ? $params[0] : 'index';
        $this->app['control'] = !empty($params[1]) ? $params[1] : 'index';
        if (isset($params[2]) && !empty($params[2])) {
            $this->app['method'] = $params[2];
        } else {
            $this->app['method'] = 'index';
        }
        $this->app['class'] = "gateway\\app\\{$this->app['app']}\\control\\" . ucfirst($this->app['control']);

        $params[0] = $this->app['app'];
        $params[1] = $this->app['control'];
        $params[2] = $this->app['method'];
        if (isset($uriParams)) {
            $params[] = $uriParams;
        }

        //解析request参数（处理参数  ?a=b&c=d）
        $parseParams = array();
        for ($i = 3;; $i++) {
            if (!isset($params[$i])) {
                break;
            }
            if (stripos($params[$i], '?') === 0) {
                $params[$i] = substr($params[$i], 1, strlen($params[$i]));
            }
            $splitArr = preg_split("/(&|=)/",$params[$i]);
            foreach ((array)$splitArr as $split) {
                $parseParams[] = $split;
            }
        }
        //解析request参数
        for ($i = 0;; $i = $i + 2) {
            if (!isset($parseParams[$i]) || ($parseParams[$i] === '' && !isset($parseParams[$i + 1]))) {
                break;
            }
            $parseParams[$i] = urldecode($parseParams[$i]);
            //request赋值
            $this->request[$parseParams[$i]] = isset($parseParams[$i + 1]) ? urldecode($parseParams[$i + 1]) : '';
        }
        //解析post的数据
        $content = file_get_contents('php://input');
        if (!empty($content)) {
//            $content = urldecode($content);
            if (in_array(substr($content, 0, 1), ['{', ']', '<'])) {
                $this->request['data'] = $content;
            } else {
                parse_str($content, $query_arr);
                $this->request = array_merge($this->request, $query_arr);
            }
        } else {
            $this->request = array_merge($this->request, $_POST);
        }
        if (isset($_FILES) && !empty($_FILES)) {
            $this->request['file'] = $_FILES;
        }

        //设置debug模式
        $this->setDisplayErrorsMode();

        //处理format
        if (isset($this->request['fmt']) && in_array($this->request['fmt'], array('json', 'ori'))) {
            $this->app['fmt'] = $this->request['fmt'];
        } else {
            $this->app['fmt'] = 'json';
        }
        if (isset($this->request['fmt'])) {
            unset($this->request['fmt']);
        }

        return;
    }

    /**
     * 处理返回信息
     */
    public function parseResponse() {
        //执行情况统计
        $stateCon = $this->getExecState();
        //记录日志
        CommonTool::errorLog($stateCon['stateCon'], 'sys_run_con');

        if (CommonTool::loadEnv('app.show_state') == 1 && is_array($this->response)) {
            $this->response['requestinfo'] = $stateCon;
        }

        //赋值request id
        if (!isset($this->request['__show*debug__']) || $this->request['__show*debug__'] != 1) {
            //ob_end_clean() 函数会静默丢弃掉缓冲区的内容
            ob_end_clean();
        }
        if (isset($this->app['obj']) && method_exists($this->app['obj'], 'render')) {
            //如果model中设置了render方法
            $this->app['obj']->render($this->request, $this->response, $this->app);
        } else if ($this->app['fmt'] == 'ori') {
            print_r($this->response);
        } else if (PHP_VERSION_ID >= 50400 && is_array($this->response)) {
            header('Content-type: application/json; charset=utf-8'); //json
            $this->response['request_id'] = $this->requestId;
            echo json_encode($this->response);
//            echo json_encode($this->response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } else if (is_array($this->response)) {
            header('Content-type: application/json; charset=utf-8'); //json
            $this->response['request_id'] = $this->requestId;
            echo json_encode($this->response);
        } else if (stripos($this->response, '<') === 0) {
            header('Content-type: application/xml; charset=utf-8'); //
            echo $this->response;
        } else if (stripos($this->response, '{') === 0 || stripos($this->response, '[') === 0) {
            header('Content-type: application/json; charset=utf-8'); //json
            echo $this->response;
        } else {
            echo $this->response;
        }
    }

    /**
     * Method setState
     * 设置执行中状态
     *
     * @param $classFlag
     * @author xy.wu
     * @since 2020/4/7 9:17
     */
    public function setState($classFlag)
    {
        if (CommonTool::loadEnv('app.show_state') != 1) {
//            return;
        }
        $this->stateCon[$classFlag]['time']['start'] = CommonTool::getMillisecond();
        $this->stateCon[$classFlag]['memory']['start'] = memory_get_usage();
    }

    /**
     * Method getExecState
     * 获取执行统计
     *
     * @return array
     * @author xy.wu
     * @since 2020/4/7 9:03
     */
    public function getExecState()
    {
        if (CommonTool::loadEnv('app.show_state') != 1) {
//            return;
        }
        $stateConTime = $stateConMemory = array();
        foreach ($this->stateCon as $key=>$value) {
            if (!isset($value['time']['end'])) {
                $this->stateCon[$key]['time']['end'] = CommonTool::getMillisecond();
            }
            if (!isset($value['memory']['end'])) {
                $this->stateCon[$key]['memory']['end'] = memory_get_usage();
            }
            $this->stateCon[$key]['time']['du'] = $this->stateCon[$key]['time']['end'] - $this->stateCon[$key]['time']['start'];
            $this->stateCon[$key]['memory']['du'] = $this->stateCon[$key]['memory']['end'] - $this->stateCon[$key]['memory']['start'];
            $this->stateCon[$key]['memory']['fm'] = ($this->stateCon[$key]['memory']['du'] / 1024 / 1024);
            $stateConTime[] = "{$key}.time=" . $this->stateCon[$key]['time']['du'];
            $stateConMemory[] = "{$key}.memory=" . $this->stateCon[$key]['memory']['fm'];
        }
        $this->stateCon['stateCon'] = implode(';', $stateConTime) . ';' . implode(';', $stateConMemory);

        return $this->stateCon;
    }

    /**
     * Method getRequestId
     * 获取requestid
     *
     * @return string
     * @author xy.wu
     * @since 2020/3/27 14:29
     */
    public function getRequestId() {
        return $this->requestId;
    }

    /**
     * Method formatJson
     * 格式化json
     *
     * @param $data
     * @return false|string
     * @author xy.wu
     * @since 2020/4/3 21:46
     */
    public function formatJson($data)
    {
        if (PHP_VERSION_ID >= 50400 && is_array($data)) {
            $data['request_id'] = $this->requestId;
            $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        } else if (is_array($data)) {
            $data['request_id'] = $this->requestId;
            $json = json_encode($data);
        } else {
            $json = $data;
        }
        return $json;
    }

}