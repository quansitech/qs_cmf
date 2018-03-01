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
class BaseController extends AddonsController{
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
            $model = D('Addons://WeixinLogin/WeixinLogin');
            $r = $model->checkOpenid($data['openid']);

            $r === true && redirect(session('cur_request_uri'));
        }
        else{
            \Think\Log::write(json_encode($data));
            E('获取不到openid');
        }
    }
    
}
