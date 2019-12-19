<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Vod
 *
 * @author XY
 */
namespace Common\Util\Vod;
use Common\Util\Vod\V20170321 as v;
use Common\Util\Vod\Sts;

class Vod {
    //put your code here
    
    public function __construct() {
        require_once '../app/Common/Util/Vod/core/Config.php';
             
    }
    
    public function getUploadAuth(){
        $regionId = 'cn-shanghai';
        $sts = new Sts();
        
        $info = $sts->getUploaderSts();
     
        $id = $info['ak'];
        $secret = $info['secret'];
        $token = $info['token'];
     
        $profile = \DefaultProfile::getProfile($regionId, $id, $secret, $token);
      
        $client = new \DefaultAcsClient($profile);      

        $createResponse = $this->_createUploadVideo($client, $regionId);
     
        $res = [];
        $res['UploadAuth'] = $createResponse->UploadAuth;
        $res['UploadAddress'] = $createResponse->UploadAddress;
        $res['VideoId'] = $createResponse->VideoId;
        $res['RequestId'] = $createResponse->RequestId;   
    
        return $res;
      
    }
    
    public function getPlayAuth($video_id){
       
        $sts = new Sts();
        
        $info = $sts->getPlayerSts();
        $id = $info['ak'];
        $secret = $info['secret'];
        $token = $info['token'];       
        
        $regionId = 'cn-shanghai';
        $profile = \DefaultProfile::getProfile($regionId, $id, $secret, $token);
        $client = new \DefaultAcsClient($profile);   
        
        try {
           $createResponse = $this->_getVideoPlayAuth($client, $regionId, $video_id);
        } catch (\Think\Exception $ex) {
            E($ex->getMessage());
        }    
        $res = [];

        $res['PlayAuth'] = $createResponse->PlayAuth;  
        
        return $res;
    }
    
    public function getPlayAddress($video_id, $definition = 'OD'){
        
        $sts = new Sts();
        
        $info = $sts->getPlayerSts();
     
        $id = $info['ak'];
        $secret = $info['secret'];
        $token = $info['token'];       
        
        $regionId = 'cn-shanghai';
        $profile = \DefaultProfile::getProfile($regionId, $id, $secret, $token);
        $client = new \DefaultAcsClient($profile);   
        
        try {
           $createResponse = $this->_getVideoInfo($client, $regionId, $video_id);
        } catch (\Think\Exception $ex) {
            E($ex->getMessage());
        }          
        
        // Definition 视频流清晰度，多个用逗号分隔，取值FD(流畅)，LD(标清)，SD(高清)，HD(超清)，OD(原画)，2K(2K)，4K(4K)
        $info = json_decode(json_encode($createResponse,JSON_FORCE_OBJECT),true);
        $address = '';
        foreach($info['PlayInfoList']['PlayInfo'] as $v){
           
            if($v['Definition'] == $definition){
                $address = $v['PlayURL'];
                break;
            }
        }
        return $address;
        
    }
    
    public function deleteVideo($video_id){
        
        $sts = new Sts();
        
        $info = $sts->getFullSts();
     
        $id = $info['ak'];
        $secret = $info['secret'];
        $token = $info['token'];       
        
        $regionId = 'cn-shanghai';
        $profile = \DefaultProfile::getProfile($regionId, $id, $secret, $token);
        
        $client = new \DefaultAcsClient($profile);   
        
        try {
           $createResponse = $this->_deleteVideo($client, $regionId, $video_id);
        } catch (\Think\Exception $ex) {
            E($ex->getMessage());
        }          

        return '';
        
    }    
    
    public function refreshUploadAuth($video_id){
        
        $sts = new Sts();
        
        $info = $sts->getFullSts();
     
        $id = $info['ak'];
        $secret = $info['secret'];
        $token = $info['token'];       
        
        $regionId = 'cn-shanghai';
        $profile = \DefaultProfile::getProfile($regionId, $id, $secret, $token);
        $client = new \DefaultAcsClient($profile);   
        
        try {
           $createResponse = $this->_refreshVideoPlayAuth($client, $regionId, $video_id);
        } catch (\Think\Exception $ex) {
            E($ex->getMessage());
        }    
        $res = [];

        $res['UploadAuth'] = $createResponse->UploadAuth;  
        
        return $res;        
    }
    
    private function _refreshVideoPlayAuth($client, $regionId, $video_id){
        
        $request = new v\RefreshUploadVideoRequest();
        //视频ID(必选)
        $request->setVideoId($video_id);
        $response = $client->getAcsResponse($request);
        return $response;          
    }

    private function _createUploadVideo($client, $regionId) {
        
       $request = new v\CreateUploadVideoRequest();
        //视频源文件标题(必选)
        $request->setTitle("视频标题");
        //视频源文件名称，必须包含扩展名(必选)
        $request->setFileName("文件名称.mov");
        //视频源文件字节数(可选)
        $request->setFileSize(0);
        //视频源文件描述(可选)
        $request->setDescription("视频描述");
        //上传所在区域IP地址(可选)
        $request->setIP("127.0.0.1");
        //视频标签，多个用逗号分隔(可选)
        $request->setTags("标签1,标签2");
        //视频分类ID(可选)
        $request->setCateId(0);
      
       $response = $client->getAcsResponse($request);
       return $response;
    } 
    
    private function _getVideoPlayAuth($client, $regionId,$video_id) {
        
       $request = new v\GetVideoPlayAuthRequest();
       $request->setAcceptFormat('JSON');
       $request->setRegionId($regionId);
       $request->setVideoId($video_id);            //视频ID
       $response = $client->getAcsResponse($request);
       return $response;
    } 
    
    private function _getVideoInfo($client, $regionId, $video_id){
        
       $request = new v\GetPlayInfoRequest();
       $request->setAcceptFormat('JSON');
       $request->setRegionId($regionId);
       $request->setVideoId($video_id);            //视频ID
       $response = $client->getAcsResponse($request);
       return $response;        
        
    }
    
    private function _deleteVideo($client, $regionId, $video_id){
        
       $request = new v\DeleteVideoRequest();
       $request->setAcceptFormat('JSON');
       $request->setVideoIds($video_id);            //视频ID
       $response = $client->getAcsResponse($request);
       return $response;        
        
    }    
    
}
