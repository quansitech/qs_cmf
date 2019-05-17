#!/usr/bin/env php
<?php
ini_set('display_errors', true);
error_reporting(E_ERROR);
set_time_limit(0);

define('MODE_NAME', 'cli'); // 自定义cli模式
define('BIND_MODULE', 'Home');  // 绑定到Home模块
define('BIND_CONTROLLER', 'Elastic'); // 绑定到Queue控制器
define('BIND_ACTION', 'index'); // 绑定到index方法

define('APP_DEBUG', true);
define('APP_DIR', realpath('./'));

// 定义应用目录
define('UPLOAD_DIR', realpath('../www/Uploads'));

require '../core/ThinkPHP.php';

