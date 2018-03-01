<?php
namespace Addons\WeixinLogin;
use Addons\Addon;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WeixinLoginAddon
 *
 * @author 英乐
 */
class WeixinLoginAddon extends Addon{
    //put your code here
    
    public $info = array(
        'name' => 'WeixinLogin',
        'title' => '微信授权登录',
        'description' => '实现了网页端与微信端的登录同步功能',
        'status' => 1,
        'author' => 'tider',
        'version' => '0.1'
    );
    
    public function install(){ 
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}weixin_login;");
        $model->execute("CREATE TABLE {$prefix}weixin_login ( `id` int(11) NOT NULL, `uid` int(11) NOT NULL,`openid` varchar(255) NOT NULL,`access_token` varchar(255) NOT NULL,`refresh_token` varchar(255) NOT NULL,`unionid` varchar(255) NOT NULL,`create_time` int(11) not null,`expires_in` int(11) not null)");
        $model->execute("ALTER TABLE {$prefix}weixin_login` ADD PRIMARY KEY (`id`)");
        $model->execute("ALTER TABLE {$prefix}weixin_login` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
        $this->createHook('mpAutoLogin', '微信端自动登录');
        $this->createHook('wxBindUser', '用户与微信账号的绑定');
        $this->createHook('wxCancelBind', '取消微信绑定');
        $this->createHook('wxUserInfo', '获取用户信息');
        return true;
    }
    
    public function uninstall(){
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}weixin_login;");
        $this->delHook('mpAutoLogin');
        $this->delHook('wxBindUser');
        $this->delHook('wxCancelBind');
        $this->delHook('wxUserInfo');
        return true;
    }
    
    public function mpAutoLogin(){
        $config = $this->getConfig();
        if(is_weixin() && !session('?weixin_token') && !isLogin()){
            session('cur_request_uri', strip_tags($_SERVER[C('URL_REQUEST_URI')]));
            $scope = 'snsapi_base';
            $weixin = new WeixinSDK();
            $url = $weixin->getMpRequestCode($config['WeiXinKEYM'], 'http://' . SITE_URL . addons_url('WeixinLogin://Base/callBack'), $scope, '1234');

            session('weixin_token', true);
            redirect($url);
        }
    }
    
    public function wxBindUser(&$user_id){
        $weixin_token = session('weixin_token');
        $weixin_login_ent = D('Addons://WeixinLogin/WeixinLogin')->where(array('openid' => $weixin_token['openid'], 'unionid' => $weixin_token['unionid']))->find();
        if($weixin_login_ent){
            $weixin_login_ent['uid'] = $user_id;
            D('Addons://WeixinLogin/WeixinLogin')->save($weixin_login_ent);
        }
    }
    
    public function wxCancelBind(&$user_id){
        if(is_weixin()){
            $map['uid'] = $user_id;
            $data['uid'] = 0;
            D('Addons://WeixinLogin/WeixinLogin')->where($map)->save($data);
        }
    }
    
    public function wxUserInfo(&$data){
        if(session('?weixin_userinfo')){
            $data = session('weixin_userinfo');
        }
    }
    
}
