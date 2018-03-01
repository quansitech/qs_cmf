<?php
namespace Common\Util;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class QQSDK{
    
    private $_get_request_code_url = 'https://graph.qq.com/oauth2.0/authorize';
    private $_get_access_token_url = 'https://graph.qq.com/oauth2.0/token';
    private $_api_base = 'https://graph.qq.com/';
    private $_appid;
    private $_appkey;
    private $_grant_type = 'authorization_code';
    
    public function __construct() {
        $this->_appid = C('QQ_APPID');
        $this->_appkey = C('QQ_APPKEY');
    }
    
    
    public function getRequestCodeUrl($callback_url){
        $params = array(
            'client_id'     => $this->_appid,
            'redirect_uri'  => $callback_url,
            'response_type' => 'code',
            'scope' => 'get_user_info',
        );
        return $this->_get_request_code_url . '?' . http_build_query($params);
    }

    public function getAccessToken($code, $callback_url){
        $params = array(
                'client_id'     => $this->_appid,
                'client_secret' => $this->_appkey,
                'grant_type'    => $this->_grant_type,
                'code'          => $code,
                'redirect_uri'  => $callback_url,
        );

        $data = $this->_http($this->_get_access_token_url, $params, 'POST');
        $this->Token = $this->_parseToken($data);
        return $this->Token;
    }
    
    private function _parseToken($result){
        $data = array();
        parse_str($result, $data);
        if($data['access_token'] && $data['expires_in']){
            $data['openid'] = $this->_openid($data);
            return $data;
        } else
            E("获取腾讯QQ ACCESS_TOKEN 出错：{$result}");
    }
    
    public function call($api, $token, $param = '', $method = 'GET'){
        /* 腾讯QQ调用公共参数 */
        $params = array(
            'oauth_consumer_key' => $this->_appid,
            'access_token'       => $token['access_token'],
            'openid'             => $this->_openid($token),
            'format'             => 'json'
        );
        
        $data = $this->_http($this->_url($api), $this->_param($params, $param), $method);
        return json_decode($data, true);
    }
    
    private function _openid($data){
        if(isset($data['openid']))
            return $data['openid'];
        elseif($data['access_token']){
            $data = $this->_http($this->_url('oauth2.0/me'), array('access_token' => $data['access_token']));
            $data = json_decode(trim(substr($data, 9), " );\n"), true);
            if(isset($data['openid']))
                return $data['openid'];
            else
                E("获取用户openid出错：{$data['error_description']}");
        } else {
            E('没有获取到openid！');
        }
    }
    
    private function _param($params, $param){
        if(is_string($param))
            parse_str($param, $param);
        return array_merge($params, $param);
    }
    
    private function _url($api, $fix = ''){
        return $this->_api_base . $api . $fix;
    }
    
    private function _http($url, $params, $method = 'GET', $header = array(), $multi = false){
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
                E('不支持的请求方式！');
        }
        
        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if($error) E('请求发生错误：' . $error);
        return  $data;
    }
}

