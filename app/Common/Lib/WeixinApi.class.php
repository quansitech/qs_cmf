<?php
namespace Common\Lib;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class WeixinApi{
    
    private $_get_access_token_url = "https://api.weixin.qq.com/cgi-bin/token";
    private $_error_msg;
    
    public function getError(){
        return $this->_error_msg;
    }
    
    private function _getAccessToken(){
        $ent = M("WeixinToken")->find();
        
        try{
            if(!$ent || ($ent['create_date'] + ($ent['expires_in'])) <= time()){
                return $this->_getAccessTokenFromWx();
            }
            else{
                return $ent['access_token'];
            }
        }
        catch (\Think\Exception $ex){
            $this->_error_msg = $ex->getMessage();
            return false;
        }
    }
    
    private function _getAccessTokenFromWx(){
        $params = array(
            "grant_type" => "client_credential",
            "appid" => C('WX_APPID'),
            "secret" => C('WX_APPSECRET'),
            );
        $data = http($this->_get_access_token_url, $params);
        $data = json_decode($data, true);
        if(isset($data['access_token'])){
            M("WeixinToken")->where("1=1")->delete();
            $data['create_date'] = time();
            M('WeixinToken')->add($data);
            return $data['access_token'];
        }
        else{
            E($data['errmsg']);
        }
    }
    
    public function sendTemplateMsg($touser, $template_id, $url, $data, $repeat_times = 0){
        $api_url = "https://api.weixin.qq.com/cgi-bin/message/template/send";
        
        $token = $this->_getAccessToken();
        
        $api_url .= "?access_token=" . $token;
        
        if($token === false){
            return false;
        }
        
        $params = array(
            "touser" => $touser,
            "template_id" => $template_id,
            "url" => $url,
            "data" => $data
        );
        
        try{
            $return_data = http($api_url , json_encode($params), "POST", array(), true);
            $return_data = json_decode($return_data, true);
            if(isset($return_data['errcode']) && $return_data['errcode'] == 0){
                return $return_data['msgid'];
            }
            else{
                //token过期, 重新获取token, 重试次数限制3次
                if(strpos($return_data['errmsg'], 'invalid credential') !== false){
                    $repeat_times++;
                    if($repeat_times > 3){
                        E('sendTemplateMsg function be called more than 3 times!');
                    }
                    $this->_getAccessTokenFromWx();
                    $msgid = $this->sendTemplateMsg($touser, $template_id, $url, $data, $repeat_times);
                    return $msgid;
                }
                E($return_data["errmsg"]);
            }
        } catch (\Think\Exception $ex) {
            $this->_error_msg = $ex->getMessage();
            return false;
        }
    }
    
}

