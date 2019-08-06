#!/usr/bin/env php
<?php
set_time_limit(0);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = \Dotenv\Dotenv::create(__DIR__ . '/..' );
$dotenv->load();

define('MODE_NAME', 'cli'); // 自定义cli模式
define('BIND_MODULE', 'Home');  // 绑定到Home模块
define('BIND_CONTROLLER', 'Elastic'); // 绑定到Queue控制器
define('BIND_ACTION', 'index'); // 绑定到index方法

define('APP_DEBUG', env("APP_DEBUG", true));
define('APP_DIR', realpath('./'));

// 定义应用目录
define('UPLOAD_DIR', realpath('../www/Uploads'));

require '../vendor/tiderjian/think-core/src/ThinkPHP.php';

