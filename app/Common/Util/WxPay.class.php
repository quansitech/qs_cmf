<?php
namespace Common\Util;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class WxPay{
    
    private $_tools;
    
    public function __construct() {
        import('Common.Util.WxPay.lib.WxPayApi');
        import('Common.Util.WxPay.JsApiPay');
        import('Common.Util.WxPay.NativePay');
        import('Common.Util.WxPay.log');
        
        $logHandler= new \CLogFileHandler("../logs/".date('Y-m-d').'.log');
        \Log::Init($logHandler, 15);
        $this->_tools = new \JsApiPay();
    }
    
    public function unifiedOrder($para){
        
        $openId = $this->_tools->GetOpenid();

        //②、统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($para['body']);
        $input->SetAttach($para['attach']);
        $input->SetOut_trade_no($para['out_trade_no']);
        $input->SetTotal_fee($para['fee'] * 100);
        $input->SetNotify_url($para['notify_url']);
        $input->SetTrade_type($para['trade_type']);
        $input->SetOpenid($openId);
        return \WxPayApi::unifiedOrder($input);
    }
    
    public function GetPayUrl($para){
        $notify = new \NativePay();
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($para['body']);
        $input->SetAttach($para['attach']);
        $input->SetOut_trade_no($para['out_trade_no']);
        $input->SetTotal_fee($para['fee'] * 100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetNotify_url($para['notify_url']);
        $input->SetTrade_type($para['trade_type']);
        $input->SetProduct_id($para['out_trade_no']);
        $result = $notify->GetPayUrl($input);
        return $result["code_url"];
    }
    
    public function GetJsApiParameters($order){
        return $this->_tools->GetJsApiParameters($order);
    }
    
    public function GetEditAddressParameters(){
        return $this->_tools->GetEditAddressParameters();
    }
}



