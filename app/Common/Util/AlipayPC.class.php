<?php
namespace Common\Util;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AlipayPC
 *
 * @author 英乐
 */
class AlipayPC {
    
    public function __construct() {
        import('Common.Util.AlipayPC.alipay_submit');
        import('Common.Util.AlipayPC.alipay_notify');
        $this->_alipay_submit = new \AlipaySubmit(self::getConfig());
        $this->_alipay_notify = new \AlipayNotify(self::getConfig());
    }
    
    public function submit($out_trade_no, $subject, $body, $amount, $p_config='', $extend_common_param = ''){
        
        $config = self::getConfig();
        if($p_config != ''){
            $config = array_merge($config, $p_config);
        }
        
        $subject = str_replace('&', '', $subject);
        $body = str_replace('&', '', $body);
        
        $parameter = array(
                    //"service" => "create_direct_pay_by_user",
                    "service" => 'create_donate_trade_p',
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
                    "_input_charset"	=> 'utf-8',
                    "extra_common_param" => $extend_common_param
            );
        
        $html_text = $this->_alipay_submit->buildRequestForm($parameter, 'get', '确认');

        header("Content-type:text/html;charset=utf-8");
        echo $html_text;
    }
    
    public function verifyReturn(&$param){
        $param = $this->_input();
        return $this->_alipay_notify->verifyReturn();
    }
    
    public function verifyNotify(&$param){
        $param = $this->_input();
        return $this->_alipay_notify->verifyNotify();
    }
    
    private function _input(){
        $param['out_trade_no'] = I('out_trade_no');
        $buyer_info['buyer_email'] = I('buyer_email');
        $buyer_info['buyer_id'] = I('buyer_id');
        $buyer_info['out_trade_no'] = I('out_trade_no');
        $buyer_info['trade_no'] = I('trade_no');
        $buyer_info['trade_status'] = I('trade_status');
        $param['buyer_info'] = $buyer_info;
        $param['trade_status'] = I('trade_status') == 'TRADE_FINISHED' || I('trade_status') == 'TRADE_SUCCESS' ? true : false;
        return $param;
    }
    
    static function getConfig(){
        $config['partner'] = C('ALIPAY_PARTNER');
        $config['key'] = C('ALIPAY_KEY');
        $config['seller_email'] = C('ALIPAY_SELLER_ACCOUNT');
        $config['sign_type'] = 'MD5';
        $config['input_charset'] = 'utf-8';
        $config['cacert'] = APP_DIR . DIRECTORY_SEPARATOR . 'Common'. DIRECTORY_SEPARATOR . 'Util\\AlipayPC\\cacert.pem';
        $config['transport'] = 'http';
        $config['notify_url'] = 'http://' . SITE_URL . '/Api/Alipay/notify';
        $config['return_url'] = 'http://' . SITE_URL . '/Api/Alipay/retn';
        return $config;
    }
}
