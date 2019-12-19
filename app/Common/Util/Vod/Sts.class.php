<?php
/*
 * 在您使用STS SDK前，请仔细阅读RAM使用指南中的角色管理部分，并阅读STS API文档
 *
 */

namespace Common\Util\Vod;
use Common\Util\Vod\sts as ss;

class Sts{
    
    public function __construct() {
        require_once '../app/Common/Util/Vod/core/Config.php';
    }
    
    public function getUploaderSts(){
   
        if(S('voduploader_ak')){
            $res = [];
            $res['ak'] = S('voduploader_ak');
            $res['secret'] = S('voduploader_secret');
            $res['token'] = S('voduploader_token');       
            return $res;
        }
        
        // 你需要操作的资源所在的region，STS服务目前只有杭州节点可以签发Token，签发出的Token在所有Region都可用
        // 只允许子用户使用角色
        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", C('VOD_ACCESS_KEY'), C('VOD_ACCESS_SECRET'));
       
        $client = new \DefaultAcsClient($iClientProfile);
        
        // 角色资源描述符，在RAM的控制台的资源详情页上可以获取
        $roleArn = C('UPLOADER_ROLE_RAN');


// 在扮演角色(AssumeRole)时，可以附加一个授权策略，进一步限制角色的权限；
// 详情请参考《RAM使用指南》
// 此授权策略表示读取所有OSS的只读权限
$policy=<<<POLICY
{
  "Statement": [
    {
      "Action": [
        "*"
      ],
      "Effect": "Allow",
      "Resource": "*"
    }
  ],
  "Version": "1"
}
POLICY;

        $request = new ss\AssumeRoleRequest();
        // RoleSessionName即临时身份的会话名称，用于区分不同的临时身份
        // 您可以使用您的客户的ID作为会话名称
        $request->setRoleSessionName("pan");
        $request->setRoleArn($roleArn);
        $request->setPolicy($policy);
        $request->setDurationSeconds(3600);
        $response = $client->doAction($request);
       
        try {
            $info = json_decode(json_encode($response,JSON_FORCE_OBJECT),true);
        } catch (\Think\Exception $ex) {
           E($ex->getMessage());
        }
      
        $body = $info['body'];
        
        $body = json_decode($body, true);      
        
        $ak = $body['Credentials']['AccessKeyId'];
        $secret = $body['Credentials']['AccessKeySecret'];
        $token = $body['Credentials']['SecurityToken'];  
        
        S('voduploader_ak', $ak, 3000);
        S('voduploader_secret', $secret, 3000);
        S('voduploader_token', $token, 3000);           
        
        return array('ak' => $ak, 'secret' => $secret, 'token' => $token);
        
    }
    
    public function getPlayerSts(){
        
//        if(S('vodplayer_ak')){
//            $res = [];
//            $res['ak'] = S('vodplayer_ak');
//            $res['secret'] = S('vodplayer_secret');
//            $res['token'] = S('vodplayer_token');
//            return $res;
//        }
        
        
        
        // 你需要操作的资源所在的region，STS服务目前只有杭州节点可以签发Token，签发出的Token在所有Region都可用
        // 只允许子用户使用角色
        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou",  C('VOD_ACCESS_KEY'), C('VOD_ACCESS_SECRET'));
       
        $client = new \DefaultAcsClient($iClientProfile);
        
        // 角色资源描述符，在RAM的控制台的资源详情页上可以获取
        $roleArn = C('PLAY_ROLE_RAN');


// 在扮演角色(AssumeRole)时，可以附加一个授权策略，进一步限制角色的权限；
// 详情请参考《RAM使用指南》
// 此授权策略表示读取所有OSS的只读权限
$policy=<<<POLICY
{
  "Statement": [
    {
      "Action": [
        "*"
      ],
      "Effect": "Allow",
      "Resource": "*"
    }
  ],
  "Version": "1"
}
POLICY;

        $request = new ss\AssumeRoleRequest();
        // RoleSessionName即临时身份的会话名称，用于区分不同的临时身份
        // 您可以使用您的客户的ID作为会话名称
        $request->setRoleSessionName("pan");
        $request->setRoleArn($roleArn);
        $request->setPolicy($policy);
        $request->setDurationSeconds(3600);
        $response = $client->doAction($request);
       
        try {
            $info = json_decode(json_encode($response,JSON_FORCE_OBJECT),true);
        } catch (\Think\Exception $ex) {
           E($ex->getMessage());
        }
      
        $body = $info['body'];
        
        $body = json_decode($body, true);      
        $ak = $body['Credentials']['AccessKeyId'];
        $secret = $body['Credentials']['AccessKeySecret'];
        $token = $body['Credentials']['SecurityToken'];  
        
        S('vodplayer_ak', $ak, 3000);
        S('vodplayer_secret', $secret, 3000);
        S('vodplayer_token', $token, 3000);    
            
        return array('ak' => $ak, 'secret' => $secret, 'token' => $token);

    }
    
    public function getFullSts(){
        
        // 你需要操作的资源所在的region，STS服务目前只有杭州节点可以签发Token，签发出的Token在所有Region都可用
        // 只允许子用户使用角色
        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou",  C('VOD_ACCESS_KEY'), C('VOD_ACCESS_SECRET'));
       
        $client = new \DefaultAcsClient($iClientProfile);
    
        $roleArn = C('FULL_ROLE_RAN');


        $request = new ss\AssumeRoleRequest();
        // RoleSessionName即临时身份的会话名称，用于区分不同的临时身份
        // 您可以使用您的客户的ID作为会话名称
        $request->setRoleSessionName("pan");
        $request->setRoleArn($roleArn);

        $request->setDurationSeconds(3600);
        $response = $client->doAction($request);
       
        try {
            $info = json_decode(json_encode($response,JSON_FORCE_OBJECT),true);
        } catch (\Think\Exception $ex) {
           E($ex->getMessage());
        }
      
        $body = $info['body'];
        
        $body = json_decode($body, true);      
        
        $ak = $body['Credentials']['AccessKeyId'];
        $secret = $body['Credentials']['AccessKeySecret'];
        $token = $body['Credentials']['SecurityToken'];  
     
        return array('ak' => $ak, 'secret' => $secret, 'token' => $token);        
        
    }
}




