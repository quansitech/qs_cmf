<?php
// 应用入口文件
ini_set('display_errors', '0');

if(!function_exists('show_bug')){
    function show_bug($object){
        echo "<pre style='color:red'>";
        var_dump($object);
        echo "</pre>";
    }
}

define('ROOT_PATH', __DIR__);

//require __DIR__ . '/vendor/tiderjian/think-core/src/Common/functions.php';
//require __DIR__ . '/app/Common/Common/function.php';
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ );
$dotenv->load();

// 引入ThinkPHP入口文件
require 'vendor/tiderjian/think-core/src/ThinkPHP.php';

