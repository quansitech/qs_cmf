<?php
namespace Common\Util;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AlipayWap
 *
 * @author 英乐
 */
class AlipayWapNew {
    public function __construct() {
        import('Common.Util.AlipayWapNew.lib.alipay_submit');
        import('Common.Util.AlipayWapNew.lib.alipay_notify');
        $this->_alipay_submit = new \AlipaySubmit(self::getConfig());
        $this->_alipay_notify = new \AlipayNotify(self::getConfig());
    }
    
    public function submit($out_trade_no, $subject, $body, $amount, $p_config = ''){
        
        $config = self::getConfig();
        
        if($p_config != ''){
            $config = array_merge($config, $p_config);
        }
        
        $subject = str_replace('&', '', $subject);
        $body = str_replace('&', '', $body);
        
        $parameter = array(
            "service"       => $config['service'],
            "partner"       => $config['partner'],
            "seller_id"  => $config['seller_id'],
            "payment_type"	=> $config['payment_type'],
            "notify_url"	=> $config['notify_url'],
            "return_url"	=> $config['return_url'],
            "_input_charset"	=> trim(strtolower($config['input_charset'])),
            "out_trade_no"	=> $out_trade_no,
            "subject"	=> $subject,
            "total_fee"	=> $amount,
            "show_url"	=> "",
            "app_pay"	=> "Y",//启用此参数能唤起钱包APP支付宝
            "body"	=> $body,
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
        $alipay_config['partner']		= C('ALIPAY_PARTNER');

        //收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
        $alipay_config['seller_id']	= $alipay_config['partner'];

        // MD5密钥，安全检验码，由数字和字母组成的32位字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
        $alipay_config['key']			= C('ALIPAY_KEY');
        // 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
        $alipay_config['notify_url'] = 'http://' . SITE_URL . '/Api/Alipay/notify';

        // 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
        $alipay_config['return_url'] = 'http://' . SITE_URL . '/Api/Alipay/retn';

        //签名方式
        $alipay_config['sign_type']    = strtoupper('MD5');

        //字符编码格式 目前支持utf-8
        $alipay_config['input_charset']= strtolower('utf-8');

        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        $alipay_config['cacert']    = APP_DIR . DIRECTORY_SEPARATOR . 'Common'. DIRECTORY_SEPARATOR . 'Util\\AlipayWapNew\\cacert.pem';

        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $alipay_config['transport']    = 'http';

        // 支付类型 ，无需修改
        $alipay_config['payment_type'] = "4";

        // 产品类型，无需修改
        $alipay_config['service'] = "alipay.wap.create.direct.pay.by.user";
        
        return $alipay_config;
    }
}
