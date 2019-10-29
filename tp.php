<?php
// 应用入口文件
ini_set('display_errors', '1');

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
if(!function_exists('fsockopen')) die('require open fscokopen');
if(!function_exists('exif_imagetype')) die('require exif');

if(!function_exists('show_bug')){
    function show_bug($object){
        echo "<pre style='color:red'>";
        var_dump($object);
        echo "</pre>";
    }
}

//require __DIR__ . '/vendor/tiderjian/think-core/src/Common/functions.php';
//require __DIR__ . '/app/Common/Common/function.php';
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::create(__DIR__ );
$dotenv->load();

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
defined('APP_DEBUG') || define('APP_DEBUG', env("APP_DEBUG", true));

defined('_PHP_FILE_') || define('_PHP_FILE_',  '');
defined('__ROOT__') || define('__ROOT__', '');


// 定义应用目录
defined('APP_NAME') || define('APP_NAME', 'app');
defined('APP_PATH') || define('APP_PATH',__DIR__ . '/' . APP_NAME . '/');
defined('APP_DIR') || define('APP_DIR', realpath(__DIR__ . '/' . APP_NAME));
defined('WWW_DIR') || define('WWW_DIR', __DIR__ . '/www');
defined('TPL_PATH') || define('TPL_PATH', APP_DIR . '/Tpl/');
defined('UPLOAD_PATH') || define('UPLOAD_PATH', __ROOT__ . '/Uploads');
defined('UPLOAD_DIR') || define('UPLOAD_DIR', WWW_DIR . DIRECTORY_SEPARATOR . 'Uploads');
defined('SECURITY_UPLOAD_PATH') || define('SECURITY_UPLOAD_PATH', __DIR__ . '/' . APP_NAME . '/Uploads');
defined('SECURITY_UPLOAD_DIR') || define('SECURITY_UPLOAD_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'Uploads');
defined('RULE_DIR') || define('RULE_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'Common/Rule');
defined('CRON_DIR') || define("CRON_DIR", APP_DIR . DIRECTORY_SEPARATOR . 'Cron');
defined('CODER_DIR') || define('CODER_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'Common/Coder');
defined('ADDON_PATH') || define('ADDON_PATH', APP_PATH . 'Addons/');
defined('LARA_DIR') || define('LARA_DIR', __DIR__  .  '/lara');


////执行https规则的网址
//$https_url_arr = array(
////    '/index.php/admin',
////    '/index.php/home/index/login',
////    '/index.php/home/register',
////    '/index.php/home/support/donation_step',
////    '/index.php/home/support/donation_step01',
////    '/index.php/home/support/security_donate',
////    '/index.php/home/support/donatemonthly_step',
////    '/index.php/home/user/editdonatemonthly_step',
////    '/index.php/home/user/donatemonthlyview'
//);
//
////http和https都可以访问的网址
//$both_url_arr = array(
////    '/index.php/api',
////    '/index.php/home/index/comment'
//);
//
//$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//foreach($https_url_arr as $v){
//    if(strpos(strtolower($_SERVER['PHP_SELF']), $v) !== false){
//        $url =  'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//        break;
//    }
//}
//
//$http_prefix = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : (isset($_SERVER['HTTPS'])? 'https'  : 'http');
//
//$target_url = $http_prefix . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//
//foreach($both_url_arr as $v){
//    if(strpos(strtolower($_SERVER['PHP_SELF']), $v) !== false){
//        $url =  $target_url;
//        break;
//    }
//}
//
////不是设定规则内地址，进行重定向
//if($url != $target_url){
//
//    header("Location: $url", true, 301);
//    exit();
//}

//show_bug($_SERVER);

// 引入ThinkPHP入口文件
require 'vendor/tiderjian/think-core/src/ThinkPHP.php';

