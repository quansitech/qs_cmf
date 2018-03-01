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
class AlipayWap {
    public function __construct() {
        import('Common.Util.AlipayWap.lib.alipay_submit');
        import('Common.Util.AlipayWap.lib.alipay_notify');
        $this->_alipay_submit = new \AlipaySubmit(self::getConfig());
        $this->_alipay_notify = new \AlipayNotify(self::getConfig());
    }
    
    public function submit($out_trade_no, $subject, $body, $amount, $p_config = '', $extend_common_param = ''){
        
        $config = self::getConfig();
        
        if($p_config != ''){
            $config = array_merge($config, $p_config);
        }
        
        $subject = str_replace('&', '', $subject);
        $body = str_replace('&', '', $body);
        
        //请求业务参数详细
        $req_data = '<direct_trade_create_req>'
                . '<notify_url>' . $config['notify_url'] . '</notify_url><call_back_url>' 
                . $config['return_url'] . '</call_back_url><seller_account_name>' 
                . trim($config['seller_email']) . '</seller_account_name><out_trade_no>' 
                . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' 
                . $amount . '</total_fee><merchant_url>' . $config['merchant_url'] 
                . '</merchant_url></direct_trade_create_req>';
        
        $para_token = array(
		"service" => "alipay.wap.trade.create.direct",
		"partner" => trim($config['partner']),
		"sec_id" => trim($config['sign_type']),
		"format"	=> $config['format'],
		"v"	=> $config['v'],
		"req_id"	=> $config['req_id'],
		"req_data"	=> $req_data,
		"_input_charset"	=> trim(strtolower($config['input_charset']))
        );
        
        $html_text = $this->_alipay_submit->buildRequestHttp($para_token);

        //URLDECODE返回的信息
        $html_text = urldecode($html_text);

        //解析远程模拟提交后返回的信息
        $para_html_text = $this->_alipay_submit->parseResponse($html_text);

        //获取request_token
        $request_token = $para_html_text['request_token'];
        
        /**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/

        //业务详细
        $req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
        //必填
        
        $parameter = array(
		"service" => "alipay.wap.auth.authAndExecute",
		"partner" => trim($config['partner']),
		"sec_id" => trim($config['sign_type']),
		"format"	=> $config['format'],
		"v"	=> $config['v'],
		"req_id"	=> $config['req_id'],
		"req_data"	=> $req_data,
		"_input_charset"	=> trim(strtolower($config['input_charset']))
        );
        
        $html_text = $this->_alipay_submit->buildRequestForm($parameter, 'get', '确认');
        echo $html_text;
    }
    
    public function verifyReturn(&$param){
        if($this->_alipay_notify->verifyReturn()){
            $param['trade_status'] = I('result') == 'success' ? true : false;
            return true;
        }
        else{
            return false;
        }
    }
    
    public function verifyNotify(&$param){
        if($this->_alipay_notify->verifyNotify()){
            $config = self::getConfig();
            $doc = new \DOMDocument();	
            if ($config['sign_type'] == 'MD5') {
                    $doc->loadXML($_POST['notify_data']);
            }

            if ($config['sign_type'] == '0001') {
                    $doc->loadXML($this->_alipay_notify->decrypt($_POST['notify_data']));
            }
            if(!empty($doc->getElementsByTagName("notify")->item(0)->nodeValue) ) {
                $param['out_trade_no'] = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
                $trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue ;
                $buyer_info['out_trade_no'] = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
                $buyer_info['buyer_email'] = $doc->getElementsByTagName( "buyer_email" )->item(0)->nodeValue;
                $buyer_info['buyer_id'] = $doc->getElementsByTagName("buyer_id")->item(0)->nodeValue;
                $buyer_info['trade_no'] = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
                $buyer_info['trade_status'] = $trade_status;
                $param['buyer_info'] = $buyer_info;
		$param['trade_status'] = $trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS' ? true : false;
                return true;
            }
            
        }
        return false;
        
    }
    
    static function getConfig(){
        $config['partner'] = C('ALIPAY_PARTNER');
        $config['key'] = C('ALIPAY_KEY');
        $config['seller_email'] = C('ALIPAY_SELLER_ACCOUNT');
        $config['format'] = 'xml';
        $config['v'] = '2.0';
        $config['req_id'] = date('Ymdhis');
        $config['sign_type'] = 'MD5';
        $config['input_charset'] = 'utf-8';
        $config['cacert'] = APP_DIR . DIRECTORY_SEPARATOR . 'Common'. DIRECTORY_SEPARATOR . 'Util\\AlipayWap\\cacert.pem';
        $config['transport'] = 'http';
        $config['notify_url'] = 'http://' . SITE_URL . '/Api/Alipay/notifyWap';
        $config['return_url'] = 'http://' . SITE_URL . '/Api/Alipay/retnWap';
        $config['merchant_url'] = 'http://' . SITE_URL . '/home/index/index';
        return $config;
    }
}
