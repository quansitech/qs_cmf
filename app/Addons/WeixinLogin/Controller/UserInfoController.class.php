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
 * Description of BaseController
 *
 * @author 英乐
 */
class UserInfoController extends AddonsController{
    //put your code here
    protected $config;
    
    public function __construct() {
        parent::__construct();
        
        $addon = new WeixinLoginAddon();
        $this->config = $addon->getConfig();
    }


    public function callBack(){
        $code = I('get.code');
        if(!$code){
            E('获取不到code');
        }
        $weixin = new WeixinSDK();
        $data = $weixin->getAccessToken($this->config['WeiXinKEYM'], $this->config['WeixinSecretM'], $code);
        
        if(isset($data['openid'])){
            session('weixin_token', $data);
            $model = D('Addons://WeixinLogin/WeixinLogin');
            $update_ent = $model->where(array('openid' => $data['openid'], 'union_id' => $data['unionid']))->find();
            if($update_ent){
                $data['create_time'] = time();
                $update_ent = array_merge($update_ent, $data);
                $model->save($update_ent);
            }
            else{
                $data['create_time'] = time();
                $model->add($data);
            }
            
            
            $map['unionid'] = $data['unionid'];
            $map['uid'] = array('neq', 0);
            $ent = $model->where($map)->find();
            if($ent){
                if($model->weixinLogin($ent['uid']) === false){
                    E($model->getError());
                }
            }
            else{
                $weixin_user_data = $weixin->getUserInfo($data['access_token'], $data['openid']);
                if(isset($weixin_user_data['errcode'])){
                    E($weixin_user_data['errmsg']);
                }
                else{
                    session('weixin_userinfo', $weixin_user_data);
                }
            }
            redirect(session('cur_request_uri'));
        }
        else{
            \Think\Log::write(json_encode($data));
            E('获取不到openid');
        }
    }
    
    
    
}
