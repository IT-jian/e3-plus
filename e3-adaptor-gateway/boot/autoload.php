<?php

//注册autoload
spl_autoload_register(function ($class) {
    $class = preg_replace('/gateway/', '', $class, 1);
    $class = substr($class, 1, strlen($class));
    $class = str_replace('\\', '/', $class);
    $file = ROOT_PATH . $class . '.php';
    if (file_exists($file)) {
        include $file;
    }
});