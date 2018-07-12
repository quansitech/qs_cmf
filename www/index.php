<?php
// 应用入口文件
ini_set('display_errors', '1');
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
if(!function_exists('fsockopen')) die('require open fscokopen');
if(!function_exists('exif_imagetype')) die('require exif');

function show_bug($object){
    echo "<pre style='color:red'>";
    var_dump($object);
    echo "</pre>";
}

function show_bug_ajax($object){
    header('Content-Type:application/json; charset=utf-8');
    exit(json_encode($object,0));
}

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', true);

define('_PHP_FILE_',  '');
define('__ROOT__', '');


// 定义应用目录
define('APP_NAME', 'app');
define('APP_PATH','../' . APP_NAME . '/');
define('APP_DIR', realpath('../' . APP_NAME));
define('WWW_DIR', __DIR__);
define('TPL_PATH', APP_DIR . '/Tpl/');
define('UPLOAD_PATH', __ROOT__ . '/Uploads');
define('UPLOAD_DIR', WWW_DIR . DIRECTORY_SEPARATOR . 'Uploads');
define('SECURITY_UPLOAD_PATH', '../' . APP_NAME . '/Uploads');
define('SECURITY_UPLOAD_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'Uploads');
define('RULE_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'Common/Rule');
define("CRON_DIR", APP_DIR . DIRECTORY_SEPARATOR . 'Cron');
define('CODER_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'Common/Coder');
define('ADDON_PATH', APP_PATH . 'Addons/');


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
require '../core/ThinkPHP.php';

