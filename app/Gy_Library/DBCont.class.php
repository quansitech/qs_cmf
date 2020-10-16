<?php

namespace Gy_Library;

class DBCont{
    
    const APPLICAT_STATUS_WAITING = 0;
    const APPLICAT_STATUS_PASS = 1;
    const APPLICAT_STATUS_FAIL = 2;
    
    const LEVEL_MODULE = 1;
    const LEVEL_CONTROLLER = 2;
    const LEVEL_ACTION = 3;
    
    const FORBIDDEN_STATUS = 0;
    const NORMAL_STATUS = 1;
    
    const PUBLISH_STATUS = 1;
    const UNPUBLISH_STATUS = 0;
    
    const SUCCESS_STATUS = 1;
    const FAIL_STATUS = 0;

    const DISPLAY_STATUS_FRONTPAGE = 1;
    const DISPLAY_STATUS_NONE = 0;
    
    const USER_GENDER_MALE = 1;
    const USER_GENDER_FEMALE = 2;
    
    const CODE_TYPE_ID = 1;
    const CODE_TYPE_PASSPORT = 2;
    
    const DELETE_AUDIT_STATUS = 3;
    const RETURN_AUDIT_STATUS = 2;
    const PASS_AUDIT_STATUS = 1;
    const WAIT_AUDIT_STATUS = 0;
    
    const YES_BOOL_STATUS = 1;
    const NO_BOOL_STATUS = 0;
    
    const YES_PAY_STATUS = 1;
    const NO_PAY_STATUS = 0;
    
    const ALIPAY_PAY_CHANNEL = 1;
    const WEIXIN_PAY_CHANNEL = 2;
    
    const WEB_CLIENT_TYPE = 1;
    const MOBILE_WEB_CLIENT_TYPE = 2;

    static private $_applicat_status = array(
        self::APPLICAT_STATUS_WAITING => '申请中',
        self::APPLICAT_STATUS_PASS => '通过',
        self::APPLICAT_STATUS_FAIL => '失败'
    );

    static private $_client_type = array(
        self::WEB_CLIENT_TYPE => '网页',
        self::MOBILE_WEB_CLIENT_TYPE => '手机网页'
    );
    
    static private $_code_type = array(
        self::CODE_TYPE_ID => '身份证',
        self::CODE_TYPE_PASSPORT => '护照'
    );
    
    static private $_user_gender = array(
        self::USER_GENDER_MALE => '男',
        self::USER_GENDER_FEMALE => '女',
    );
    
    static private $_display_status = array(
        self::DISPLAY_STATUS_FRONTPAGE => '首页',
        self::DISPLAY_STATUS_NONE => '无'
    );
    
    static private $_success_status = array(
        self::SUCCESS_STATUS => '成功',
        self::FAIL_STATUS => '失败'
    );
    
    static private $_publish_status = array(
        self::PUBLISH_STATUS => '已发布',
        self::UNPUBLISH_STATUS => '未发布'
    );
    
    static private $_user_status = array(
        self::NORMAL_STATUS => '正常',
        self::FORBIDDEN_STATUS => '禁用'
    );
    
    
    static private $_status = array(
        self::NORMAL_STATUS => '正常',
        self::FORBIDDEN_STATUS => '禁用'
    );
    
    static private $_audit_status = array(
        self::PASS_AUDIT_STATUS => '已审核',
        self::WAIT_AUDIT_STATUS => '未审核'
    );
    
    static private $_audit_status_3 = array(
        self::PASS_AUDIT_STATUS => '已审核',
        self::WAIT_AUDIT_STATUS => '等待审核',
        self::RETURN_AUDIT_STATUS => '退回'
    );
    
    static private $_bool_status = array(
        self::YES_BOOL_STATUS => '是',
        self::NO_BOOL_STATUS => '否'
    );
    
    static private $_pay_status = array(
        self::YES_PAY_STATUS => '已支付',
        self::NO_PAY_STATUS => '未支付'
    );
    
    static private $_pay_channel = array(
        self::ALIPAY_PAY_CHANNEL => '支付宝',
        self::WEIXIN_PAY_CHANNEL => '微信'
    );
    
    static public function getApplicatStatus($status){
        return self::$_applicat_status[$status];
    }
    
    static public function getApplicatStatusList(){
        return self::$_applicat_status;
    }
    
    static public function getClientType($type){
        return self::$_client_type[$type];
    }
    
    static public function getClientTypeList(){
        return self::$_client_type;
    }
    
    static public function getPayChannel($pay_channel){
        return self::$_pay_channel[$pay_channel];
    }
    
    static public function getPayChannelList(){
        return self::$_pay_channel;
    }
    
    static public function getPayStatus($pay_status){
        return self::$_pay_status[$pay_status];
    }
    
    static public function getPayStatusList(){
        return self::$_pay_status;
    }
    
    static public function getBoolStatus($bool_status){
        return self::$_bool_status[$bool_status];
    }
    
    static public function getBoolStatusList(){
        return self::$_bool_status;
    }
    
    static public function getAuditStatus($audit_status){
        return self::$_audit_status[$audit_status];
    }
    
    static public function getAuditStatusList(){
        return self::$_audit_status;
    }
    
    static public function getAuditStatus3($audit_status){
        return self::$_audit_status_3[$audit_status];
    }
    
    static public function getAuditStatusList3(){
        return self::$_audit_status_3;
    }
    
    static function getCodeType($type){
        return self::$_code_type[$type];
    }
    
    static function getCodeTypeList(){
        return self::$_code_type;
    }

    static function getGender($gender){
        return self::$_user_gender[$gender];
    }
    
    static function getGenderList(){
        return self::$_user_gender;
    }
    
    static function getStatus($status){
        return self::$_status[$status];
    }
    
    static function getStatusList(){
        return self::$_status;
    }
    
    static function getUserStatus($status){
        return self::$_user_status[$status];
    }
    
    static function getUserStatusList(){
        return self::$_user_status;
    }
    
    static function getDisplayStatus($status){
        return self::$_display_status[$status];
    }
    
    static function getDisplayList(){
        return self::$_display_status;
    }
    
    
    static function getSuccessStatus($status){
        return self::$_success_status[$status];
    }
    
    static function getPublishStatus($status){
        return self::$_publish_status[$status];
    }
    
    static function getPublishStatusList(){
        return self::$_publish_status;
    }

    static function __callStatic($name, $arguments)
    {
        $getListFn = function($var_name){
            return self::$$var_name;
        };

        $getListValueFn = function($var_name) use ($arguments){

            return (self::$$var_name)[$arguments[0]];
        };

        $static_name = '_';
        if(preg_match("/get(\w+)List/", $name, $matches)){
            $static_name .= parse_name($matches[1]);
            $fn = $getListFn;
        }
        elseif(preg_match("/get(\w+)/", $name, $matches)){
            $static_name .= parse_name($matches[1]);
            $fn = $getListValueFn;
        }

        return $fn($static_name);
    }
    
}

