<?php
/*
 * File name
 * 
 * @author xy.wu
 * @since 2020/4/23 22:38
 */

//nginx兼容
if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        static $headers = [];
        if (!empty($headers)) {
            return $headers;
        }

        //获取headers
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}