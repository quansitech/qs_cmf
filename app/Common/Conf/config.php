<?php
$common_config = array(
    "QS_ADDONS" => false, //是否要开启插件系统，关闭可以减少数据库访问频率
    'DEFAULT_FILTER'        =>  'htmlspecialchars,trim', // 默认参数过滤方法 用于I函数...

    'AUTOLOAD_NAMESPACE' => array('Addons' => ADDON_PATH), //扩展模块列表
    'SHOW_PAGE_TRACE'       =>  false,

    'USER_AUTH_GATEWAY' => '/Admin/Public/login',

    'LOG_RECORD'            =>  true,

    'COOKIE_HTTPONLY' => true,

    'JS_ERROR_LOG' => true,

    'VAR_PATHINFO' => 'baobao_',

    'ENCRYPT_KEY' => 'csh',

    'COOKIE_PREFIX' => 'qs_',

    'WX_INFO_SESSION_KEY' => 'wx_info',

    'ELASTIC_ALLOW_EXCEPTION' => true,
    'ELASTICSEARCH_HOSTS' => explode(',', ENV('ELASTICSEARCH_HOSTS')),

    'QUEUE' => array(
        'type' => 'redis',
        'host' => env("QUEUE_REDIS", 'redis'),
        'port' =>  env("QUEUE_REDIS_PORT", 6379),
        'prefix' => 'queue',
        'auth' =>  '',
        'database_index' => env("QUEUE_REDIS_DATABASE", 0),
    ),

    'RESQUE_JOB_REPEAT_TIMES' => 3,

    //资源调用设置
    'ASSET' => array(
        'prefix' => injecCdntUrl() . __ROOT__ . '/Public/',
    ),

    //数据库连接配置
    'DB_TYPE'               =>  env('DB_CONNECTION', 'mysql'),     // 数据库类型
    'DB_HOST'               =>  env('DB_HOST', '127.0.0.1'), // 服务器地址
    'DB_NAME'               =>  env('DB_DATABASE', 'qs_cmf'),          // 数据库名
    'DB_USER'               =>  env('DB_USERNAME', 'root'),      // 用户名
    'DB_PWD'                =>  env('DB_PASSWORD', 'root'),          // 密码
    'DB_PORT'               =>  env('DB_PORT', '3306'),        // 端口
    'DB_PREFIX'             =>  env('DB_PREFIX', 'qs_'),    // 数据库表前缀
    'DB_FIELDTYPE_CHECK'    =>  false,       // 是否进行字段类型检查
    'DB_STRICT'             =>  env('DB_STRICT', true),
    //以下字段缓存没有其作用
    //① 如果是调试模式就不起作用
    //② false  也是不起作用
    'DB_FIELDS_CACHE'       =>  true,        // 启用字段缓存
    'DB_CHARSET'            =>  'utf8mb4',      // 数据库编码默认采用utf8

    'LANG_SWITCH_ON'        =>  true,
    'LANG_AUTO_DETECT'      =>  true, // 自动侦测语言 开启多语言功能后有效
    'LANG_LIST'             =>  'zh-cn', // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE'          =>  'l', // 默认语言切换变量

    'GY_TOKEN_ON'           =>   true,  //公益平台token机制开启
    'TOKEN_ON'              =>   true,  // 是否开启令牌验证 默认关闭
    'TOKEN_NAME'            =>   '__hash__',    // 令牌验证的表单隐藏字段名称，默认为__hash__
    'TOKEN_TYPE'            =>   'md5',  //令牌哈希验证规则 默认为MD5
    'TOKEN_RESET'           =>   true,  //令牌验证出错后是否重置令牌 默认为true

    /* 模板引擎设置 */
    'TMPL_ACTION_ERROR'     =>  APP_PATH.'Tpl/dispatch_jump.tpl', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  APP_PATH.'Tpl/dispatch_jump.tpl', // 默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE'   =>  APP_PATH.'Tpl/think_exception.tpl',// 异常页面的模板文件

    /* 错误设置 */
    'ERROR_MESSAGE'         =>  '抱歉，您访问的页面已经不存在了。',//错误显示信息,非调试模式有效
    //'ERROR_PAGE'            =>  'http://ev.t4tstudio.com/Public/error_page.html', // 错误定向页面
    'SHOW_ERROR_MSG'        =>  false,    // 显示错误信息

   // 'URL_ROUTER_ON' => true,

    'TMPL_PARSE_STRING' => array(
        '__ADDONSJS__' => __ROOT__ . '/Public/Addons'
    ),

    'INERTIA' => [
        'ssr_url' => env('INERTIA_SSR_URL'),
    ]
);

return array_merge($common_config, loadAllCommonConfig());