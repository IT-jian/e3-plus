<?php

namespace gateway\app\ftp;

require_once '../../vendor/autoload.php';

use FtpClient\FtpClient;

define('ROOT_PATH', dirname(dirname(dirname(__FILE__))));
var_dump(ROOT_PATH);

if (isset($_REQUEST['__show*debug__']) && $_REQUEST['__show*debug__'] == 1) {
    ini_set('display_errors',1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors',0);
    error_reporting(E_ALL & ~(E_STRICT|E_NOTICE|E_WARNING));
}

$gatewayConfig = include ROOT_PATH . '/config/gateway.php';

echo "<br />\n\rConnect to [{$gatewayConfig['ftp']['host']}], ssl=true, port=21, timeout=6<br \>\n\r";
try {
    $ftp = new FtpClient();
    $ftp->connect($gatewayConfig['ftp']['host'], true, 21, 6);
    $ftp->login($gatewayConfig['ftp']['user'], $gatewayConfig['ftp']['password']);

//    $dir = $ftp->scanDir('.', true);
//    print_r($dir);

    $ftp->set_option(FTP_USEPASVADDRESS,false);
    $ftp->pasv(true);

    $put = $ftp->put('./20880319030944560156_20191003.csv', ROOT_PATH . '/cache/20880319030944560156_20191001.csv', FTP_BINARY);
    var_dump($put);
//    $dir = $ftp->scanDir('.', true);
//    print_r($dir);

    $ftp->rename('./20880319030944560156_20191003.csv', './20880319030944560156_20191003-rename.csv');

    $dir = $ftp->scanDir('.', true);
    print_r($dir);

    $put = $ftp->put('./20880319030944560156_20191004.csv', ROOT_PATH . '/cache/20880319030944560156_20191001.csv', FTP_BINARY);
    var_dump($put);
//    $dir = $ftp->scanDir('.', true);
//    print_r($dir);

    $ftp->rename('./20880319030944560156_20191004.csv', './20880319030944560156_20191004-rename.csv');

    $dir = $ftp->scanDir('.', true);
    print_r($dir);

    $ftp->putAll(ROOT_PATH . '/cache', '.');

    $dir = $ftp->scanDir('.', true);
    print_r($dir);
} catch (\Exception $e) {
    print_r($e);
}

/*$conn = ftp_ssl_connect($gatewayConfig['ftp']['host'], 21, 6) or die("Could not connect");
$login = ftp_login($conn, $gatewayConfig['ftp']['user'], $gatewayConfig['ftp']['password']);
var_dump($login);

ftp_set_option($conn,FTP_USEPASVADDRESS,false);
//设置为被动模式
$pasv = ftp_pasv($conn, true);
var_dump($pasv);

$put = ftp_put($conn, './20880319030944560156_20191001.csv', ROOT_PATH . '/cache/20880319030944560156_20191001.csv', FTP_BINARY);
var_dump($put);

$size = ftp_size($conn, './20880319030944560156_20191001.csv');
var_dump($size);

ftp_close($conn);*/


