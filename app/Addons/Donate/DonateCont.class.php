<?php
namespace Addons\Donate;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DonateCont
 *
 * @author tider
 */
class DonateCont {
    const PREFIX_SIR = 1;
    const PREFIX_MADAM = 2;
    const PREFIX_CHILD = 3;
    
    static private $_prefix = array(
        self::PREFIX_SIR => '先生',
        self::PREFIX_MADAM => '女士',
        self::PREFIX_CHILD => '小朋友'
    );
    
    static function getPrefix($prefix){
        return self::$_prefix[$prefix];
    }
    
    static function getPrefixList(){
        return self::$_prefix;
    }
    
    const PUBLISH_ALLOW = 1;
    const PUBLISH_FORBID = 0;
    const PUBLISH_DOESNT_MATTER = 2;
    
    static private $_publish = array(
        self::PUBLISH_ALLOW => '是',
        self::PUBLISH_FORBID => '否',
        self::PUBLISH_DOESNT_MATTER => '无所谓'
    );
    
    static function getPublish($publish){
        return self::$_publish[$publish];
    }
    
    static function getPublishList(){
        return self::$_publish;
    }
    
    const DONATE_MAIL_TYPE_EMAIL = 0;  //扫描版捐赠票据发送至指定邮箱
    const DONATE_MAIL_TYPE_EMS = 1; //定期邮寄捐赠票据（快递到付）
    const DONATE_MAIL_TYPE_SELF = 2;  //定期上门自取捐赠票据
    const DONATE_MAIL_TYPE_NONEED = 3; //没必要
    
    static private $_donate_mail_type = array(
        self::DONATE_MAIL_TYPE_EMAIL => '扫描版捐赠票据发送至指定邮箱',
        self::DONATE_MAIL_TYPE_EMS => '定期邮寄捐赠票据（快递到付）',
        self::DONATE_MAIL_TYPE_SELF=> '定期上门自取捐赠票据',
        self::DONATE_MAIL_TYPE_NONEED=> '没必要'
    );
    
    static function getDonateMailType($type){
        return self::$_donate_mail_type[$type];
    }
    
    static function getDonateMailTypeList(){
        return self::$_donate_mail_type;
    }
    
    const DONATE_STATUS_NOPAY = 0; //待支付
    const DONATE_STATUS_PAID = 1;  //支付成功
    
    static private $_donate_status = array(
        self::DONATE_STATUS_NOPAY => '待支付',
        self::DONATE_STATUS_PAID => '支付成功'
    );
    
    static function getDonateStatus($status){
       return self::$_donate_status[$status];
   }
   
    const PAY_CHANNEL_ALIPAY = 1;
    const PAY_CHANNEL_BANK_TRANSFER = 4;
    const PAY_CHANNEL_CASH = 7;
    
    static private $_pay_channel = array(
        self::PAY_CHANNEL_ALIPAY => '支付宝',
        self::PAY_CHANNEL_BANK_TRANSFER => '银行转账',
        self::PAY_CHANNEL_CASH => '现金',
    );
    
    static function getPayChannel($value){
        return self::$_pay_channel[$value];
    }
    
    static function getPayChannelList(){
        return self::$_pay_channel;
    }

}
