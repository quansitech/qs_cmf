<?php

namespace Addons\SyncLogin\Controller;
use Home\Controller\AddonsController;


class BaseController extends AddonsController{
    
    public function __construct() {
        parent::__construct();
        
        import('Addons.SyncLogin.ThinkSDK.ThinkOauth');
    }

    //登录地址
    public function login(){
        session('tmp_token', null);
        $type= I('get.type');
        $get = I('get.');
        
        $query = array_diff($get, array('type' => $type, '_addons' => I('get._addons'), '_controller'=>I('get._controller'), '_action'=>I('get._action')));
        empty($type) && $this->error('参数错误');
        //加载ThinkOauth类并实例化一个对象
        $sns  = \ThinkOauth::getInstance($type);
        //跳转到授权页面
        redirect($sns->getRequestCodeURL($query));
    }

    //登陆后回调地址
    public function callback(){
        $code =  I('get.code');
        $type= I('get.type');

        $sns  = \ThinkOauth::getInstance($type);

        //腾讯微博需传递的额外参数
        $extend = null;
        if($type == 'tencent'){
            $extend = array('openid' => I('get.openid'), 'openkey' =>  I('get.openkey'));
        }

        if(!session('tmp_token')){
            $token = $sns->getAccessToken($code , $extend);
            session('tmp_token', $token);
        }
        else{
            $token = session('tmp_token');
        }
        $sync_login_model = D('Addons://SyncLogin/SyncLogin');
        $user_info = $sync_login_model->$type($token); //获取传递回来的用户信息
        
        //如果已经登录，则是用户点击绑定操作，省去输入用户名密码步骤
        if(isLogin()){
            $r = $sync_login_model->bindUser(session(C('USER_AUTH_KEY')), sf($token['openid']), $type, sf($token['access_token']), sf($token['refresh_token']));
            if($r === false){
                $this->error($sync_login_model->getError(), U('/home/user/bindSocial'));
            }
            else{
                $this->success('绑定成功', U('/home/user/bindSocial'));
            }
            return;
        }
        
        
        $condition = array(
            'openid' => sf($token['openid']),
            'type' => $type,
            'status' => 1,
        );
        $user_info_sync_login = D('sync_login')->where($condition)->find(); // 根据openid等参数查找同步登录表中的用户信息
        if($user_info_sync_login) {//曾经绑定过
            $user_info_user_center = D('User')->find($user_info_sync_login ['uid']); 
            if($user_info_user_center){
                $syncdata ['access_token'] = sf($token['access_token']);
                $syncdata ['refresh_token'] = sf($token['refresh_token']);
                D('sync_login')->where( array('uid' =>$user_info_user_center ['id'], 'openid' => sf($token['openid']), 'type' => $type ) )->save($syncdata); //更新Token
                $public = new \Admin\Controller\PublicController();                   
                if( $public->homeLogin($user_info_user_center['email'], '', true) !== false){    
                    $this->assign('jumpUrl', U('Home/Index/index'));
                    $this->success('同步登录成功');                
                }else{
                    $this->error($public->getError());
                }
            }else{
                $condition = array(
                    'openid' => sf($token['openid']),
                    'type' => $type
                );
                D('sync_login')->where($condition)->delete();
            }
        } else { //没绑定过，去注册页面
            //<input type="hidden" name="other_type" value="{$user.type}" >
            //<input type="hidden" name="other_openid" value="{$token.openid}" >
            //<input type="hidden" name="other_access_token" value="{$token.access_token}" >
            //<input type="hidden" name="other_refresh_token" value="{$token.refresh_token}" >
            session('third_login_user', $user_info);
            session('third_login_token', $token);
            if(isShowVerify()){
                $this->assign('verify_show', true);
            }
            $this->assign ('user_info', $user_info );
            $this->assign('meta_title', '注册');
            $this->display(T('Addons://SyncLogin@default/reg'));
        }
    }

}