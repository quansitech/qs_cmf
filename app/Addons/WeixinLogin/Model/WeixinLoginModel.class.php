<?php
namespace Addons\WeixinLogin\Model;
use Addons\WeixinLogin\WeixinSDK;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class WeixinLoginModel extends \Gy_Library\GyModel{
    protected $config;
    
    public function __construct() {
        parent::__construct();
        
        $addon = new \Addons\WeixinLogin\WeixinLoginAddon();
        $this->config = $addon->getConfig();
    }
    
    public function checkOpenid($openid){
        $weixin_ent = $this->where(array('openid' => $openid))->find();
        if($weixin_ent){
            session('weixin_token', $weixin_ent);
            if($weixin_ent['uid']){
                return $this->weixinLogin($weixin_ent['uid']);
            }
            else{
                $map['unionid'] = $weixin_ent['unionid'];
                $map['uid'] = array('neq', 0);
                $union_ent = $this->where($map)->find();
                if($union_ent){
                    $weixin_ent['uid'] = $union_ent['uid'];
                    $this->save($weixin_ent);
                    return $this->weixinLogin($weixin_ent['uid']);
                }
                else{
                    return $this->checkAccessToken($weixin_ent);
                }
            }
        }
        else{
            $this->requestUserInfo();
        }
    }
    
    public function checkAccessToken($data){
        $weixin = new WeixinSDK();
        if($data['expires_in'] + $data['create_time'] > time()){
            $weixin_user_data = $weixin->getUserInfo($data['access_token'], $data['openid']);
            session('weixin_userinfo', $weixin_user_data);
            return true;
        }
        else{
            $weixin_data = $weixin->refreshToken($this->config['WeiXinKEYM'], $data['refresh_code']);
            if(isset($weixin_data['errcode'])){
                $this->requestUserInfo();
                return false;
            }
            else{
                session('weixin_token', $weixin_data);
                $weixin_user_data = $weixin->getUserInfo($weixin_data['access_token'], $weixin_data['openid']);
                session('weixin_userinfo', $weixin_user_data);
                return true;
            }
            
        }
        
    }
    
    
    public function weixinLogin($uid){
        $user_model = D('User');
        $user_ent = $user_model->getOne($uid);
        switch ($user_ent['user_type']){
            case 'person':
                $r = $user_model->homeLogin($user_ent['telephone'], $user_ent['pwd'], $user_ent['user_type']);
                break;
            case 'company':
                $r = $user_model->homeLogin($user_ent['nick_name'], $user_ent['pwd'], $user_ent['user_type']);
                break;
            default:
                E('类型异常');
                break;
        }
        
        if($r === false){
            $this->error = $user_model->getError();
            return false;
        }
        return $r;
    }
    
    
    
    public function requestUserInfo(){
        $weixin = new WeixinSDK();
        $url = $weixin->getMpRequestCode($this->config['WeiXinKEYM'], 'http://' . SITE_URL . addons_url('WeixinLogin://UserInfo/callBack'), 'snsapi_userinfo');

        redirect($url);
    }
}
