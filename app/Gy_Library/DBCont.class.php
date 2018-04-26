<?php

namespace Gy_Library;

class DBCont{
    const GRADE_ONE = 1;
    const GRADE_TWO = 2;
    const GRADE_THREE = 3;
    const GRADE_FOUR = 4;
    const GRADE_FIVE = 5;
    const GRADE_SIX = 6;
    const GRADE_SEVEN = 7;
    const GRADE_EIGHT = 8;
    const GRADE_NINE = 9;
    
    const HELP_STATUS_UNSOLVE = 0;
    const HELP_STATUS_SOLVE = 1;
    
    const FAMILY_CONDITION_PARENTOUT = 1;
    const FAMILY_CONDITION_FATHEROUT = 2;
    const FAMILY_CONDITION_MOTHEROUT = 3;
    
    const VOLUNTEER_STATUS_APPLICATING = 1;
    const VOLUNTEER_STATUS_ACTIVE = 2;
    const VOLUNTEER_STATUS_FORBIDDEN = 0;
    
    const CHILD_STATUS_APPLICATING = 1;
    const CHILD_STATUS_ACTIVE = 2;
    const CHILD_STATUS_FORBIDDEN = 0;
    
    const EMAIL_RESULT_SUCCESS = 1;
    const EMAIL_RESULT_FAILURE = 2;
    
    const JOB_STATUS_WAITING = 1;
    const JOB_STATUS_RUNNING = 2;
    const JOB_STATUS_FAILED = 3;
    const JOB_STATUS_COMPLETE = 4;
    
    const APPLICAT_STATUS_WAITING = 0;
    const APPLICAT_STATUS_PASS = 1;
    const APPLICAT_STATUS_FAIL = 2;
    
    const ORDER_STATUS_PROCESS = 1;
    const ORDER_STATUS_FINISH = 2;
    const ORDER_STATUS_CANCEL = 3;
    
    const PROCESS_STATUS_NONE = 0;
    const PROCESS_STATUS_FINISH = 1;
    const PROCESS_STATUS_DELAY = 2;
    const PROCESS_STATUS_CHANGE = 3;
    
    const BILL_ITEM_TYPE_TOTAL = 1;
    const BILL_ITEM_TYPE_TRAVEL = 2;
    
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
    
    const PERSON_DONATOR_TYPE = 1;
    const COMPANY_DONATOR_TYPE = 2;
    
    const WEB_CLIENT_TYPE = 1;
    const MOBILE_WEB_CLIENT_TYPE = 2;
    
    const APPLY_INVOICE_STATUS = 1;
    const SEND_INVOICE_STATUS = 2;
    
    const FAVOUR_READ = 1;
    const FAVOUR_MUSIC = 2;
    const FAVOUR_PAINT =3 ;
    const FAVOUR_FOOTBALL = 4;
    const FAVOUR_BASKETBALL = 5;
    
    static private $_help_status = array(
        self::HELP_STATUS_UNSOLVE => '未解决',
        self::HELP_STATUS_SOLVE => '已解决'
    );
    
    static private $_family_condition = array(
        self::FAMILY_CONDITION_PARENTOUT => '父母都外出工作',
        self::FAMILY_CONDITION_FATHEROUT => '只是父亲外出工作',
        self::FAMILY_CONDITION_MOTHEROUT => '只是母亲外出工作'
    );
    
    static private $_grade = array(
        self::GRADE_ONE => '一年级',
        self::GRADE_TWO => '二年级',
        self::GRADE_THREE => '三年级',
        self::GRADE_FOUR => '四年级',
        self::GRADE_FIVE => '五年级',
        self::GRADE_SIX => '六年级',
        self::GRADE_SEVEN => '七年级',
        self::GRADE_EIGHT => '八年级',
        self::GRADE_NINE => '九年级'
    );
    
    static private $_volunteer_status = array(
        self::VOLUNTEER_STATUS_FORBIDDEN => '禁用',
        self::VOLUNTEER_STATUS_APPLICATING => '申请中',
        self::VOLUNTEER_STATUS_ACTIVE => '正常'
    );
    
    static private $_child_status = array(
        self::CHILD_STATUS_FORBIDDEN => '禁用',
        self::CHILD_STATUS_APPLICATING => '申请中',
        self::CHILD_STATUS_ACTIVE => '正常'
    );
    
    static private $_favour = array(
        self::FAVOUR_READ => '读书',
        self::FAVOUR_MUSIC => '音乐',
        self::FAVOUR_PAINT => '画画',
        self::FAVOUR_FOOTBALL => '足球',
        self::FAVOUR_BASKETBALL => '篮球'
    );
    
    static private $_job_status = array(
        self::JOB_STATUS_WAITING => '等待',
        self::JOB_STATUS_RUNNING => '运行中',
        self::JOB_STATUS_FAILED => '失败',
        self::JOB_STATUS_COMPLETE => '完成'
    );
    
    static private $_email_result = array(
        self::EMAIL_RESULT_SUCCESS => '成功',
        self::EMAIL_RESULT_FAILURE => '失败'
    );
    
    static private $_applicat_status = array(
        self::APPLICAT_STATUS_WAITING => '申请中',
        self::APPLICAT_STATUS_PASS => '通过',
        self::APPLICAT_STATUS_FAIL => '失败'
    );
    
    static private $_order_status = array(
        self::ORDER_STATUS_PROCESS => '进行中',
        self::ORDER_STATUS_FINISH => '已结项',
        self::ORDER_STATUS_CANCEL => '合同终止'
    );
    
    static private $_bill_item_type = array(
        self::BILL_ITEM_TYPE_TOTAL => '按总价分配',
        self::BILL_ITEM_TYPE_TRAVEL => '差旅费'
    );
    
    static private $_process_status = array(
        self::PROCESS_STATUS_NONE => '',
        self::PROCESS_STATUS_FINISH => '已完成',
        self::PROCESS_STATUS_DELAY => '延期',
        self::PROCESS_STATUS_CHANGE => '改期'
    );
    
    
    static private $_invoice_status = array(
        self::APPLY_INVOICE_STATUS => '申请中',
        self::SEND_INVOICE_STATUS => '已寄出'
    );

    static private $_clothes_size = array(
        1 => 'XS(130/64A)（儿童）',
        2 => 'S(160/76A)',
        3 => 'M(165/80A)',
        4 => 'L(175/88A)',
        5 => 'XL(180/92A)',
        );

    static private $_client_type = array(
        self::WEB_CLIENT_TYPE => '网页',
        self::MOBILE_WEB_CLIENT_TYPE => '手机网页'
    );
    
    static private $_code_type = array(
        self::CODE_TYPE_ID => '身份证',
        self::CODE_TYPE_PASSPORT => '护照'
    );
    
    static private $_donator_type = array(
        self::PERSON_DONATOR_TYPE => '个人',
        self::COMPANY_DONATOR_TYPE => '企业'
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
    
    static public function getHelpStatus($status){
        return self::$_help_status[$status];
    }
    
    static public function getHelpStatusList(){
        return self::$_help_status;
    }
    
    static public function getFamilyCondition($condition){
        return self::$_family_condition[$condition];
    }
    
    static public function getFamilyConditionList(){
        return self::$_family_condition;
    }
    
    static public function getGrade($grade){
        return self::$_grade[$grade];
    }
    
    static public function getGradeList(){
        return self::$_grade;
    }
    
    static public function getChildStatus($status){
        return self::$_child_status[$status];
    }
    
    static public function getChildStatusList(){
        return self::$_child_status;
    }
    
    static public function getVolunteerStatus($status){
        return self::$_volunteer_status[$status];
    }
    
    static public function getVolunteerStatusList(){
        return self::$_volunteer_status;
    }
    
    static public function getFavour($favour){
        return self::$_favour[$favour];
    }
    
    static public function getFavourList(){
        return self::$_favour;
    }
    
    static public function getJobStatus($status){
        return self::$_job_status[$status];
    }
    
    static public function getJobStatusList(){
        return self::$_job_status;
    }
    
    static public function getApplicatStatus($status){
        return self::$_applicat_status[$status];
    }
    
    static public function getApplicatStatusList(){
        return self::$_applicat_status;
    }
    
    static public function getEmailResult($result){
        return self::$_email_result[$result];
    }
    
    static public function getEmailResultList(){
        return self::$_email_result;
    }
    
    static public function getBillItemType($type){
        return self::$_bill_item_type[$type];
    }
    
    static public function getBillItemTypeList(){
        return self::$_bill_item_type;
    }
    
    static public function getOrderStatus($status){
        return self::$_order_status[$status];
    }
    
    static public function getOrderStatusList(){
        return self::$_order_status;
    }
    
    static public function getProcessStatus($status){
        return self::$_process_status[$status];
    }
    
    static public function getProcessStatusList(){
        return self::$_process_status;
    }
    
    static public function getInvoiceStatus($status){
        return self::$_invoice_status[$status];
    }
    
    static public function getInvoiceStatusList(){
        return self::$_invoice_status;
    }
    
    static public function getClientType($type){
        return self::$_client_type[$type];
    }
    
    static public function getClientTypeList(){
        return self::$_client_type;
    }
    
    static public function getDonatorType($type){
        return self::$_donator_type[$type];
    }
    
    static public function getDonatorTypeList(){
        return self::$_donator_type;
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


    static public function getClothesSizeList(){
        return self::$_clothes_size;
    }
    
    static public function getClothesSize($size){
        return self::$_clothes_size[$size];
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

