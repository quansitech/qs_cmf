<?php
namespace Addons\Donate\Util;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Alipay
 *
 * @author tider
 */
class Alipay {
    //put your code here
    
    private $_alipay_notify;
    private $_config;
    private $_alipay_submit;
    
    public function __construct() {
        import('Addons.Donate.Util.Alipay.alipay_notify');
        import('Addons.Donate.Util.Alipay.alipay_submit');
        $this->_config = self::getConfig();
        $this->_alipay_submit = new \AlipaySubmit($this->_config);
        
        $this->_alipay_notify = new \AlipayNotify($this->_config);
        
        //清除多余的GET参数,否则会影响alipay的签名验证
        if($_GET){
            unset($_GET['_addons']);
            unset($_GET['_controller']);
            unset($_GET['_action']);
        }
    }
    
    static function getConfig(){
        $addon = new \Addons\Donate\DonateAddon();
        
        $addon_config = $addon->getConfig();
        
        $config['alipay_trade_no_prefix'] = $addon_config['alipay_trade_no_prefix'];
        $config['partner'] = $addon_config['alipay_partner'];
        $config['key'] = $addon_config['alipay_key'];
        $config['seller_email'] = $addon_config['alipay_acount'];
        $config['sign_type'] = 'MD5';
        $config['input_charset'] = 'utf-8';
        $config['cacert'] = ADDON_PATH . 'Donate'. DIRECTORY_SEPARATOR . 'Util\\Alipay\\cacert.pem';
        $config['transport'] = 'http';
        $config['notify_url'] = 'http://' . trim(SITE_URL, '/') . addons_url('Donate://Donate/notify');
        $config['return_url'] = 'http://' . trim(SITE_URL, '/') . addons_url('Donate://Donate/retn');
        return $config;
    }
    
    public function submit($out_trade_no, $subject, $body, $amount, $extend_common_param = ''){
        
        $config = $this->_config;
        
        $parameter = array(
                    "service" => "create_direct_pay_by_user",
                    "partner" => $config['partner'],
                    "payment_type"	=> 4,  //即时到账
                    "notify_url"	=> $config['notify_url'],
                    "return_url"	=> $config['return_url'],
                    "seller_email"	=> $config['seller_email'],
                    "out_trade_no"	=> $out_trade_no,
                    "subject"	=> $subject,
                    "total_fee"	=> $amount,
                    "body"	=> $body,
                    "show_url"	=> '',
                    "anti_phishing_key"	=> '',
                    "exter_invoke_ip"	=> '',
                    "_input_charset"	=> 'utf-8',
                    "extra_common_param" => $extend_common_param
            );
        
        $html_text = $this->_alipay_submit->buildRequestForm($parameter, 'get', '确认');
        header("Content-type:text/html;charset=utf-8");
        echo $html_text;
    }
    
    public function verify($type){
        $verify_result = false;
        if($type == 'return'){
            $verify_result = $this->_alipay_notify->verifyReturn();
        }
        else if($type == 'notify'){
            $verify_result = $this->_alipay_notify->verifyNotify();
        }
        return $verify_result;
    }
}
