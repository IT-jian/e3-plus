<?php

//默认打开php错误
ini_set('display_errors',1);
error_reporting(E_ALL);

//设置默认时区
ini_set('date.timezone','Asia/Shanghai');

//定义根目录
define('ROOT_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

//加载系统自动加载
require_once ROOT_PATH . 'boot/autoload.php';

//加载vendor自动加载
require_once ROOT_PATH . 'vendor/autoload.php';

//加载系统共用函数
require_once ROOT_PATH . 'library/wsc-lib/function.php';

use gateway\boot\GatewayBoot AS Boot;

$GLOBALS['gb'] = new Boot();

$GLOBALS['gb']->run();

/**
 * 获取gateway boot对象
 * @return mixed
 */
function &GB() {
    return $GLOBALS['gb'];
}

