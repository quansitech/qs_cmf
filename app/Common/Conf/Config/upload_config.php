<?php
return array(

    //阿里云oss
    'ALIOSS_ACCESS_KEY_ID' => env('ALIOSS_ACCESS_KEY_ID'),
    'ALIOSS_ACCESS_KEY_SECRET' => env('ALIOSS_ACCESS_KEY_SECRET'),

    'UPLOAD_FILE_SIZE' => 50,

    /* 编辑器图片上传相关配置 */
    'UPLOAD_TYPE_UEDITOR' => array(
		'mimes'    => '', //允许上传的文件MiMe类型
		'maxSize'  => 10*1024*1024, //上传的文件大小限制 (0-不做限制)
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
        'oss_host' => env("ALIOSS_HOST"),
        'os_upload_meta' => array('Cache-Control' => 'max-age=2592001'),
        'tos_host' => env('VOLC_HOST'),
//        'vendorType' => 'volcengine_tos',
        'cos_host' => env('COS_HOST'),
//        'upload_tos_host' => env('VOLC_HOST'),
    ),

    /* 编辑器图片上传相关配置 */
    'UPLOAD_TYPE_EDITOR' => array(
		'mimes'    => '', //允许上传的文件MiMe类型
		'maxSize'  => 10*1024*1024, //上传的文件大小限制 (0-不做限制)
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
        'oss_host' => env("ALIOSS_HOST"),
        'os_upload_meta' => array('Cache-Control' => 'max-age=2592001'),
        'tos_host' => env('VOLC_HOST'),
//        'vendorType' => 'volcengine_tos',
        'cos_host' => env('COS_HOST'),
//        'upload_tos_host' => env('VOLC_HOST'),
    ),

    'UPLOAD_TYPE_AUDIO' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 100*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'mp3,wav,cd,ogg,wma,asf,rm,real,ape,midi', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date','Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/', //保存根路径
        'savePath' => 'audio/', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        'oss_host' => env("ALIOSS_HOST"),
        'oss_meta' => array('Cache-Control' => 'max-age=2592001'),
    ),

    /* 图片上传相关配置 */
    'UPLOAD_TYPE_IMAGE' => array(
		'mimes'    => 'image/jpeg,image/png,image/gif,image/bmp,image/heic,image/heif', //允许上传的文件MiMe类型
		'maxSize'  => 100*1024*1024, //上传的文件大小限制 (0-不做限制)
		'exts'     => 'jpg,gif,png,jpeg,heic,heif', //允许上传的文件后缀
		'autoSub'  => true, //自动子目录保存文件
		'subName'  => array('date','Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
		'rootPath' => './Uploads/', //保存根路径
		'savePath' => 'image/', //保存路径
		'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
		'saveExt'  => '', //文件保存后缀，空则使用原后缀
		'replace'  => false, //存在同名是否覆盖
		'hash'     => true, //是否生成hash编码
		'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
		'oss_host' => env("ALIOSS_HOST"),
		'oss_meta' => array('Cache-Control' => 'max-age=2592001'),
        'os_upload_meta' => array('Cache-Control' => 'max-age=2592001', 'Content-Disposition' => 'attachment;filename=__title__'),
        'tos_host' => env('VOLC_HOST'),
//        'vendorType' => 'volcengine_tos',
        'cos_host' => env('COS_HOST'),
    ),

    'UPLOAD_TYPE_VIDEO' => array(
		'mimes'    => '', //允许上传的文件MiMe类型
		'maxSize'  => 500*1024*1024, //上传的文件大小限制 (0-不做限制)
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
        'oss_host' => env("ALIOSS_HOST"),
    ),

    /* 文件上传相关配置 */
    'UPLOAD_TYPE_FILE' => array(
		'mimes'    => '', //允许上传的文件MiMe类型
		'maxSize'  => 50*1024*1024, //上传的文件大小限制 (0-不做限制)
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
        'oss_host' => env("ALIOSS_HOST"),
    ),

    'UPLOAD_TYPE_JOB_IMAGE' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 10*1024*1024, //上传的文件大小限制 (0-不做限制)
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
