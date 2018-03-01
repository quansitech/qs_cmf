<?php
namespace Common\Util;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class AliOss{
    private $_bucket;
    private $_end_point;
    private $_oss_client;
    private $_config;
    
    public function __construct() {
        spl_autoload_register(function($class){
            $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
            $file = __DIR__ . DIRECTORY_SEPARATOR . $path . '.php';
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
    
    public function getOssClient($type){
        $config = C('UPLOAD_TYPE_' . strtoupper($type));
        if(!$config){
            E('上传类型' . $type . '不存在!');
        }
        $this->_config = $config;
        
        if(!$config['oss_host']){
            E($type . '这不是oss上传配置类型!');
        }
        
        if(!preg_match('/https*:\/\/([\w-_]+?)\.[\w-_\.]+/', $config['oss_host'], $match)){
            E($type . '类型上传配置项中匹配不到bucket项');
        }
        
        $this->_bucket = $match[1];
        $this->_end_point = str_replace($this->_bucket . '.', '', $config['oss_host']);
        $this->_oss_client = new \OSS\OssClient(C('ALIOSS_ACCESS_KEY_ID'), C('ALIOSS_ACCESS_KEY_SECRET'), $this->_end_point);
        return $this;
    }
    
    public function uploadFile($file, $options){
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $object = self::genOssObjectName($this->_config, '.' . $ext);
        $header_options = array(\OSS\OssClient::OSS_HEADERS => $options);
        return $this->_oss_client->uploadFile($this->_bucket, $object, $file, $header_options);
    }
    
    public function signUrl($object, $timeout){
        
        $signedUrl = $this->_oss_client->signUrl($this->_bucket, $object, $timeout);
        return $signedUrl;
    }
    
    public static function genOssObjectName($config, $ext = ''){
        $sub_name = self::_getName($config['subName']);
        $pre_path = $config['rootPath'] . $config['savePath'] . $sub_name .'/';
        $save_name = self::_getName($config['saveName']);
        $dir = trim(trim($pre_path . $save_name, '.'), '/');
        if($ext){
            $dir .= $ext;
        }
        return $dir;
    }
    
    private static function _getName($rule){
        $name = '';
        if(is_array($rule)){ //数组规则
            $func     = $rule[0];
            $param    = (array)$rule[1];
            $name = call_user_func_array($func, $param);
        } elseif (is_string($rule)){ //字符串规则
            if(function_exists($rule)){
                $name = call_user_func($rule);
            } else {
                $name = $rule;
            }
        }
        return $name;
    }
}

