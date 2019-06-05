<?php
return array(
    'TEMPLATE_MAP' => [
        'package/tiderjian/think-core/src/Library/Qscmf/View/default/common/head.html' => 'app/Common/View/head.html'
    ],

    'AUTOLOAD_NAMESPACE' => array(
        'Addons' => ADDON_PATH
    ), //扩展模块列表

    'LOG_RECORD'            =>  true,

    'UPLOAD_FILE_SIZE' => 5,

    'JS_ERROR_LOG' => true,

    'VAR_PATHINFO' => 'baobao_',

    'ENCRYPT_KEY' => 'csh',

    'COOKIE_PREFIX' => 'qs_',

    //通过$_SERVER数组获取当前访问的http协议的关键值
    'HTTP_PROTOCOL_KEY' => 'HTTP_X_FORWARDED_PROTO',

    //阿里云oss
    'ALIOSS_ACCESS_KEY_ID' => '',
    'ALIOSS_ACCESS_KEY_SECRET' => '',

    'ELASTIC_ALLOW_EXCEPTION' => true,
    'ELASTICSEARCH_HOSTS' => explode(',', ENV('ELASTICSEARCH_HOSTS')),

    'QUEUE' => array(
        'type' => 'redis',
        'host' => '127.0.0.1',
        'port' =>  '6379',
        'prefix' => 'queue',
        'auth' =>  '',
    ),

    'UPLOAD_FILE_SIZE' => 5,

    //数据库连接配置
    'DB_TYPE'               =>  env('DB_CONNECTION', 'mysql'),     // 数据库类型
    'DB_HOST'               =>  env('DB_HOST', '127.0.0.1'), // 服务器地址
    'DB_NAME'               =>  env('DB_DATABASE', 'qs_cmf'),          // 数据库名
    'DB_USER'               =>  env('DB_USERNAME', 'root'),      // 用户名
    'DB_PWD'                =>  env('DB_PASSWORD', 'root'),          // 密码
    'DB_PORT'               =>  env('DB_PORT', '3306'),        // 端口
    'DB_PREFIX'             =>  env('DB_PREFIX', 'qs_'),    // 数据库表前缀
    'DB_FIELDTYPE_CHECK'    =>  false,       // 是否进行字段类型检查
    //以下字段缓存没有其作用
    //① 如果是调试模式就不起作用
    //② false  也是不起作用
    'DB_FIELDS_CACHE'       =>  true,        // 启用字段缓存
    'DB_CHARSET'            =>  'utf8mb4',      // 数据库编码默认采用utf8

    /* 错误设置 */
    'ERROR_MESSAGE'         =>  '抱歉，您访问的页面已经不存在了。',//错误显示信息,非调试模式有效
    //'ERROR_PAGE'            =>  'http://ev.t4tstudio.com/Public/error_page.html', // 错误定向页面

    /* 编辑器图片上传相关配置 */
    'UPLOAD_TYPE_EDITOR' => array(
		'mimes'    => '', //允许上传的文件MiMe类型
		'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
		'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
		'autoSub'  => true, //自动子目录保存文件
		'subName'  => array('date','Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
		'rootPath' => './Uploads/editor/', //保存根路径
		'savePath' => '', //保存路径
		'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
		'saveExt'  => '', //文件保存后缀，空则使用原后缀
		'replace'  => false, //存在同名是否覆盖
		'hash'     => true, //是否生成hash编码
		'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ),

    /* 图片上传相关配置 */
    'UPLOAD_TYPE_IMAGE' => array(
		'mimes'    => 'image/jpeg,image/png,image/gif,image/bmp', //允许上传的文件MiMe类型
		'maxSize'  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
		'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
		'autoSub'  => true, //自动子目录保存文件
		'subName'  => array('date','Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
		'rootPath' => './Uploads/', //保存根路径
		'savePath' => 'image/', //保存路径
		'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
		'saveExt'  => '', //文件保存后缀，空则使用原后缀
		'replace'  => false, //存在同名是否覆盖
		'hash'     => true, //是否生成hash编码
		'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
		'oss_host' => 'https://csh-pub-resp.oss-cn-shenzhen.aliyuncs.com',
		'oss_meta' => array('Cache-Control' => 'max-age=2592000'),
    ),

    'UPLOAD_TYPE_VIDEO' => array(
		'mimes'    => '', //允许上传的文件MiMe类型
		'maxSize'  => 30*1024*1024, //上传的文件大小限制 (0-不做限制)
		'exts'     => 'mp4,avi,rmvb,rm,mpg,mpeg,wmv,mkv,flv', //允许上传的文件后缀
		'autoSub'  => true, //自动子目录保存文件
		'subName'  => array('date','Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
		'rootPath' => './Uploads/', //保存根路径
		'savePath' => 'video/', //保存路径
		'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
		'saveExt'  => '', //文件保存后缀，空则使用原后缀
		'replace'  => false, //存在同名是否覆盖
		'hash'     => true, //是否生成hash编码
		'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ),

    /* 文件上传相关配置 */
    'UPLOAD_TYPE_FILE' => array(
		'mimes'    => '', //允许上传的文件MiMe类型
		'maxSize'  => 10*1024*1024, //上传的文件大小限制 (0-不做限制)
		'exts'     => 'doc,docx,xls,xlsx,pdf,ppt,txt,rar', //允许上传的文件后缀
		'autoSub'  => true, //自动子目录保存文件
		'subName'  => array('date','Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
		'rootPath' => './Uploads/', //保存根路径
		'savePath' => 'file/', //保存路径
		'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
		'saveExt'  => '', //文件保存后缀，空则使用原后缀
		'replace'  => false, //存在同名是否覆盖
		'hash'     => true, //是否生成hash编码
		'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ),

    'UPLOAD_TYPE_SFILE' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 10*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'doc,docx,xls,xlsx,pdf,ppt,txt,rar', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date','Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => '../app/Uploads/', //保存根路径
        'savePath' => 'file/', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        'security' => true,
    ),

    'UPLOAD_TYPE_SIMAGE' => array(
        'mimes'    => 'image/jpeg,image/png,image/gif,image/bmp', //允许上传的文件MiMe类型
        'maxSize'  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date','Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => '../app/Uploads/', //保存根路径
        'savePath' => 'image/', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        'security' => true,
        'oss_host' => 'https://csh-private-resp.oss-cn-shenzhen.aliyuncs.com'
    ),

    'UPLOAD_TYPE_JOB_IMAGE' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date','Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => '../www/Uploads/', //保存根路径
        'savePath' => 'image/', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ),


);

