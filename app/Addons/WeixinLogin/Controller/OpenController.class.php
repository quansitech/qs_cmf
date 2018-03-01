<?php
namespace Addons\WeixinLogin\Controller;
use Home\Controller\AddonsController;
use Addons\WeixinLogin\WeixinSDK;
use Addons\WeixinLogin\WeixinLoginAddon;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OpenController
 *
 * @author 英乐
 */
class OpenController extends AddonsController{
    //put your code here
    protected $config;


    public function __construct() {
        parent::__construct();
        
        $addon = new WeixinLoginAddon();
        $this->config = $addon->getConfig();
    }
    
    public function login(){
        $weixin = new WeixinSDK();
        $scope = 'snsapi_login,snsapi_userinfo';
        $request_url = $weixin->getOpenRequestCode($this->config['WeiXinKEYW'], 'http://' . SITE_URL . addons_url('WeixinLogin://Open/callBack'), $scope, '1234');
    
        redirect($request_url);
    }
    
    public function callBack(){
        $code =  I('get.code');
        if(!$code){
            E('获取不到code');
        }
        
        $weixin = new WeixinSDK();
        $data = $weixin->getAccessToken($this->config['WeiXinKEYW'], $this->config['WeixinSecretW'], $code);

        if(isset($data['openid'])){
            $model = D('Addons://WeixinLogin/WeixinLogin');
            $map['unionid'] = $data['unionid'];
            $map['uid'] = array('neq',0);
            
            $ent = $model->where($map)->find();
            if($ent){
                $r = $model->weixinLogin($ent['uid']);
                if($r === false){
                    E($model->getError());
                }
                else{
                    redirect(U('/home/index/index'));
                }
            }
            else{
                session('weixin_token', $data);
                $userinfo = $weixin->getUserInfo($data['access_token'], $data['openid']);
                if(isset($userinfo['errcode'])){
                    E($userinfo['errmsg']);
                }
                else{
                    session('weixin_userinfo', $userinfo);
                    
                    //跳转到注册or登录界面
                    redirect(session('cur_request_uri'));
                }
            }
        }
        else{
            E($data['errmsg']);
        }
    }
}
