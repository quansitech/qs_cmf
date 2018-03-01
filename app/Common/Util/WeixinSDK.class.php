<?php
namespace Common\Util;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WeixinSDK
 *
 * @author 英乐
 */
class WeixinSDK {
    //put your code here
    
    protected $mp_code_url = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    
    protected $open_code_url = 'https://open.weixin.qq.com/connect/qrconnect';
    
    protected $access_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    
    protected $get_userinfo_url = 'https://api.weixin.qq.com/sns/userinfo';
    
    protected $refresh_token_url = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';
    
    public function getMpRequestCode($app_key, $call_back, $scope, $state){
        $params = array(
            'appid' => $app_key,
            'redirect_uri' => $call_back,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state
        );
        
        return $this->mp_code_url . '?' . http_build_query($params) . '#wechat_redirect';
    }
    
    
    public function getOpenRequestCode($app_key, $call_back, $scope, $state){
        $params = array(
            'appid' => $app_key,
            'redirect_uri' => $call_back,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state
        );
        
        return $this->open_code_url . '?' . http_build_query($params) . '#wechat_redirect';
    }
    
    public function getAccessToken($app_key, $app_secret, $code){
        $params = array(
                'appid'     => $app_key,
                'secret' => $app_secret,
                'grant_type'    => 'authorization_code',
                'code'          => $code
        );
        
        $data = $this->http($this->access_token_url, $params, 'post');
        $data = json_decode($data, true);
        return $data;
    }
    
    public function refreshToken($app_key, $refresh_token){
        $params = array(
                'appid'     => $app_key,
                'refresh_token' => $refresh_token,
                'grant_type'    => 'refresh_token'
        );
        
        $data = $this->http($this->refresh_token_url, $params, 'post');
        $data = json_decode($data, true);
        return $data;
    }
    
    public function getUserInfo($access_token, $openid){
        $p = array(
            'access_token'       => $access_token,
            'openid'             => $openid,
            'lang' => 'zh_CN'
        );
        $d = $this->http($this->get_userinfo_url, $p, 'post');
        return json_decode($d, true);
    }
    
    public function http($url, $params, $method = 'GET', $header = array(), $multi = false){
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header
        );

        /* 根据请求类型设置特定参数 */
        switch(strtoupper($method)){
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                //判断是否传输文件
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new Exception('不支持的请求方式！');
        }
        
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if($error) throw new Exception('请求发生错误：' . $error);
        return  $data;
    }
}
