<?php
/**
 * 抓取远程图片
 * User: Jinqn
 * Date: 14-04-14
 * Time: 下午19:18
 */
set_time_limit(0);
include("Uploader.class.php");

/* 上传配置 */
$config = array(
    "pathFormat" => $CONFIG['catcherPathFormat'],
    "maxSize" => $CONFIG['catcherMaxSize'],
    "allowFiles" => $CONFIG['catcherAllowFiles'],
    "oriName" => "remote.png"
);
$fieldName = $CONFIG['catcherFieldName'];

/* 抓取远程图片 */
$list = array();
if (isset($_POST[$fieldName])) {
    $source = $_POST[$fieldName];
} else {
    $source = $_GET[$fieldName];
}

$oss = $_GET['oss'];
if($oss){
  $common_config = include "../../../../../app/Common/Conf/config.php";
  $type = $_GET['type'];
  if(!$type){
    $type = 'image';
  }
  $oss_type = $common_config['UPLOAD_TYPE_' . strtoupper($type)];
  $url = $oss_type['oss_host'];
  $rt = parse_url($url);
  $arr = explode('.', $rt['host']);
  $bucket = array_shift($arr);
  $endpoint = $rt['scheme'] . '://' . join('.', $arr);

  $oss_config = array(
      "ALIOSS_ACCESS_KEY_ID" => $common_config['ALIOSS_ACCESS_KEY_ID'],
      "ALIOSS_ACCESS_KEY_SECRET" => $common_config["ALIOSS_ACCESS_KEY_SECRET"],
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

  $oss_client = new \OSS\OssClient($oss_config['ALIOSS_ACCESS_KEY_ID'], $oss_config['ALIOSS_ACCESS_KEY_SECRET'], $oss_config['end_point']);
  $header_options = array(\OSS\OssClient::OSS_HEADERS => $oss_type['oss_meta']);

  foreach ($source as $imgUrl) {
      $item = new Uploader($imgUrl, $config, "remote");
      $info = $item->getFileInfo();
      $file = realpath('../../../..' . $info['url']);
      $r = $oss_client->uploadFile($oss_config['bucket'], trim($info['url'], '/'), $file, $header_options);
      unlink($file);
      $info['url'] = $r['oss-request-url'] . $oss_type['oss_style'];

      array_push($list, array(
          "state" => $info["state"],
          "url" => $info["url"],
          "size" => $info["size"],
          "title" => htmlspecialchars($info["title"]),
          "original" => htmlspecialchars($info["original"]),
          "source" => htmlspecialchars($imgUrl)
      ));
  }

  /* 返回抓取数据 */
  return json_encode(array(
      'state'=> count($list) ? 'SUCCESS':'ERROR',
      'list'=> $list
  ));
}
else{
  foreach ($source as $imgUrl) {
      $item = new Uploader($imgUrl, $config, "remote");
      $info = $item->getFileInfo();
      array_push($list, array(
          "state" => $info["state"],
          "url" => $info["url"],
          "size" => $info["size"],
          "title" => htmlspecialchars($info["title"]),
          "original" => htmlspecialchars($info["original"]),
          "source" => htmlspecialchars($imgUrl)
      ));
  }

  /* 返回抓取数据 */
  return json_encode(array(
      'state'=> count($list) ? 'SUCCESS':'ERROR',
      'list'=> $list
  ));
}
