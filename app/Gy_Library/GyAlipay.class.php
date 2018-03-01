<?php

namespace Gy_Library;

class GyAlipay{
    
    private $_alipay;
    
    public function __construct() {
        $mobile_detect = new \Common\Util\Mobile_Detect();
        if($mobile_detect->isMobile() ){
            $this->_alipay = new \Common\Util\AlipayWapNew();
        }
        else{
            $this->_alipay = new \Common\Util\AlipayPC();
        }
    }
    
    public function submit($out_trade_no, $subject, $body, $amount,$config='', $extend_common_param = ''){
        $this->_alipay->submit($out_trade_no, $subject, $body, $amount, $config, $extend_common_param);
    }
    
    public function verifyReturn(&$param){
        return $this->_alipay->verifyReturn($param);
    }
    
    public function verifyNotify(&$param){
        return $this->_alipay->verifyNotify($param);
    }
    
}

