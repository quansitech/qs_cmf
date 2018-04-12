<?php
/**
 * 上传附件和上传视频
 * User: Jinqn
 * Date: 14-04-09
 * Time: 上午10:17
 */
include "Uploader.class.php";

/* 上传配置 */
$base64 = "upload";
switch (htmlspecialchars($_GET['action'])) {
    case 'uploadimage':
        $config = array(
            "pathFormat" => $CONFIG['imagePathFormat'],
            "maxSize" => $CONFIG['imageMaxSize'],
            "allowFiles" => $CONFIG['imageAllowFiles']
        );
        $fieldName = $CONFIG['imageFieldName'];
        break;
    case 'uploadscrawl':
        $config = array(
            "pathFormat" => $CONFIG['scrawlPathFormat'],
            "maxSize" => $CONFIG['scrawlMaxSize'],
            "allowFiles" => $CONFIG['scrawlAllowFiles'],
            "oriName" => "scrawl.png"
        );
        $fieldName = $CONFIG['scrawlFieldName'];
        $base64 = "base64";
        break;
    case 'uploadvideo':
        $config = array(
            "pathFormat" => $CONFIG['videoPathFormat'],
            "maxSize" => $CONFIG['videoMaxSize'],
            "allowFiles" => $CONFIG['videoAllowFiles']
        );
        $fieldName = $CONFIG['videoFieldName'];
        break;
    case 'uploadfile':
    default:
        $config = array(
            "pathFormat" => $CONFIG['filePathFormat'],
            "maxSize" => $CONFIG['fileMaxSize'],
            "allowFiles" => $CONFIG['fileAllowFiles']
        );
        $fieldName = $CONFIG['fileFieldName'];
        break;
}


/**
 * 得到上传文件所对应的各个参数,数组结构
 * array(
 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
 *     "url" => "",            //返回的地址
 *     "title" => "",          //新文件名
 *     "original" => "",       //原始文件名
 *     "type" => ""            //文件类型
 *     "size" => "",           //文件大小
 * )
 */
/* 生成上传实例对象并完成上传 */
$up = new Uploader($fieldName, $config, $base64);
$oss = $_GET['oss'];
if($oss){
  /* 返回数据 */
  $file_info = $up->getFileInfo();

  $config = include "../../../../../app/Common/Conf/config.php";
  $type = $_GET['type'];
  if(!$type){
    $type = 'image';
  }
  $oss_type = $config['UPLOAD_TYPE_' . strtoupper($type)];
  $url = $oss_type['oss_host'];
  $rt = parse_url($url);
  $arr = explode('.', $rt['host']);
  $bucket = array_shift($arr);
  $endpoint = $rt['scheme'] . '://' . join('.', $arr);


  $oss_config = array(
      "ALIOSS_ACCESS_KEY_ID" => $config['ALIOSS_ACCESS_KEY_ID'],
      "ALIOSS_ACCESS_KEY_SECRET" => $config["ALIOSS_ACCESS_KEY_SECRET"],
      "end_point" => $endpoint,
      "bucket" => $bucket
  );

  spl_autoload_register(function($class){
      $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
      $file = "../../../../../app/Common/Util" . DIRECTORY_SEPARATOR . $path . '.php';
      if (file_exists($file)) {
          require_once $file;
      }
  });

  if($file_info['state'] != 'SUCCESS'){
      return json_encode($file_info);
  }


  $oss_client = new \OSS\OssClient($oss_config['ALIOSS_ACCESS_KEY_ID'], $oss_config['ALIOSS_ACCESS_KEY_SECRET'], $oss_config['end_point']);
  $header_options = array(\OSS\OssClient::OSS_HEADERS => $oss_type['oss_meta']);

  $file = realpath('../../../..' . $file_info['url']);

  $r = $oss_client->uploadFile($oss_config['bucket'], trim($file_info['url'], '/'), $file, $header_options);
  unlink($file);
  $file_info['url'] = $r['oss-request-url'] . $oss_type['oss_style'];
  return json_encode($file_info);
}
else{


  /**
   * 得到上传文件所对应的各个参数,数组结构
   * array(
   *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
   *     "url" => "",            //返回的地址
   *     "title" => "",          //新文件名
   *     "original" => "",       //原始文件名
   *     "type" => ""            //文件类型
   *     "size" => "",           //文件大小
   * )
   */

  /* 返回数据 */
  return json_encode($up->getFileInfo());
}
