<?php
namespace Addons\SmsBatchSend\Controller;
use Home\Controller\AddonsController;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SmsBatchController
 *
 * @author 英乐
 */
class SmsBatchController extends AddonsController{
    
    private $_sms_body_prefix = '';
    private $_sms_body_suffix = '【全思科技】';
    
    public function __construct() {
        parent::__construct();
        
        $this->_admin_function_list = array(
            'sendSms',
            'smsLog'
        );
    }
    
    public function sendSms(){
        $this->_initReceiver();
        $log_id = I('get.log_id');
        
        //邮件详情处可以点击转发给其他人跳转至该页面
        if($log_id){
            $h_log_ent = D('Addons://SmsBatchSend/SmsLogH')->getOne($log_id);
            if(!$h_log_ent){
                $this->error('该短信不存在');
            }
            $this->assign('h_log', $h_log_ent);
        }
        
        \Think\Hook::listen('getSmsBalance', $para);
        if($para['return']['status'] == 0){
            E($para['return']['err_msg']);
        }
        $balance = $para['return']['data'][0]['remain'];

        $this->assign('balance', $balance);

        $this->assign('sms_body_prefix', $this->_sms_body_prefix);
        $this->assign('sms_body_suffix', $this->_sms_body_suffix);
        $this->assign('var_list', \Admin\Lib\MsgTemplateParse::getVar());
        
        $send_id = M('SmsMenuConfig')->where(array('kname' => 'send_id'))->getField('value');
        $this->assign('send_id', $send_id);
        
        $this->display(addon_t('SmsBatchSend', 'sendSms'));
    }
    
    public function smsLog(){
        //获取短信发送状态
        \Think\Hook::listen('getSmsStatus', $para);
        
        foreach($para['return']['data'] as $item){
            $d_ent = D('Addons://SmsBatchSend/SmsLogD')->where(array('sid' => $item['msgid']))->find();
            if($item['result'] == 2){
                $d_ent['status'] = '发送成功';
            }
            else{
                $d_ent['status'] = '发送失败';
                $d_ent['error_msg'] = $item['msg'];
            }
            D('Addons://SmsBatchSend/SmsLogD')->save($d_ent);
        }
        
        
        $count = D('Addons://SmsBatchSend/SmsLogH')->getListForCount();
       
        $list_rows = C('PER_PAGE_NUM', null, false);

        $para = array();
        if($list_rows === false){
            $para = array($count);
        }
        else{
            $para = array($count, $list_rows);
        }

        $class = new \ReflectionClass('\Gy_Library\GyPage');
        $page = $class->newInstanceArgs($para);

        $pagination = $page->show();
        $this->assign('pagination', $pagination);

        $list = D('Addons://SmsBatchSend/SmsLogH')->getListForPage('', $page->nowPage, $page->listRows, 'create_date desc');
        
        foreach($list as &$v){
            $total = D('Addons://SmsBatchSend/SmsLogD')->where('hid=' . $v['id'])->count();
            $success_num = D('Addons://SmsBatchSend/SmsLogD')->getSuccessNum($v['id']);
            $fail_num = D('Addons://SmsBatchSend/SmsLogD')->getFailNum($v['id']);
            $v['total'] = $total;
            $v['sparkline'] = $success_num . ',' . $fail_num;
            $v['sparkline_str'] = $fail_num > 0 ? $success_num . '成功 ' . $fail_num . '失败' : $success_num . '成功 ';
        }
        
        $this->assign('list', $list);
        
        $log_id = M('SmsMenuConfig')->where(array('kname' => 'log_id'))->getField('value');
        $this->assign('log_id', $log_id);
        
        $this->display(addon_t('SmsBatchSend', 'smsLog'));
    }
    
    private function _initReceiver(){
        $telephones = I('get.telephones');
        $telephones && $telephones = explode(',', $telephones);
        $names = I('get.names');
        $names && $names = explode(',', $names);
        $receiver = array();
        foreach($telephones as $k => $v){
            $tmp_arr['name'] = $names[$k];
            $tmp_arr['telephone'] = $telephones[$k];
            $receiver[] = $tmp_arr;
        }
        
        $receiver && $this->assign('receiver', $receiver);
    }
    
    public function send_sms() {

        $data_post = I('post.');
        
        $to_arr = explode(',', $data_post['to']);
        
        //删除模板前缀信息，保持发送逻辑的一致性
        $sms_h_data['body'] = $data_post['body'];
        $sms_h_data['body'] = substr($sms_h_data['body'], strlen($this->_sms_body_prefix));
        $sms_h_data['body'] = substr($sms_h_data['body'], 0, strlen($data_post) - strlen($this->_sms_body_suffix));
        $sms_h_data['create_user'] = session(C('USER_AUTH_KEY'));
        $sms_h_data['create_date'] = time();
        
        //记录短信发送记录 头
        $hid = D('Addons://SmsBatchSend/SmsLogH')->add($sms_h_data);
        
        for($i=0;$i<count($to_arr);$i++){
            $args['telephone'] = $to_arr[$i];
            $body_arr[] = \Admin\Lib\MsgTemplateParse::parse($args, showHtmlContent($sms_h_data['body']));
        }
        
        $para['mobile'] = join(',', $to_arr);
        $para['content'] = join('{|}', $body_arr);
        if(count($to_arr)>1){
            \Think\Hook::listen('sendSmsBatch', $para);
        }
        else{
            \Think\Hook::listen('sendSmsOnce', $para);
        }
        
        //将sid
        foreach($para['return']['data'] as $k=>$v){
            $sid = $v['msgid'];
            $sms_d_data = array();
            $sms_d_data['mobile'] = $to_arr[$k];
            $sms_d_data['status'] = '等待发送';
            $sms_d_data['hid'] = $hid;
            $sms_d_data['sid'] = $sid;
            $sms_d_data['send_date'] = time();
            
            D('Addons://SmsBatchSend/SmsLogD')->add($sms_d_data);
        }
        
        $para['return']['id'] = $hid;
        $this->ajaxReturn($para['return']);
        
    }
    
    public function viewLog(){
        //获取短信回复
        \Think\Hook::listen('getSmsRely', $para);
        foreach($para['return']['data'] as $item){
            $d_ent = D('Addons://SmsBatchSend/SmsLogD')->where(array('sid' => $item['msgid']))->find();
            if($d_ent){
                $d_ent['rely'] = $item['content'];
                D('Addons://SmsBatchSend/SmsLogD')->save($d_ent);
            }
        }
        
        $id = I('get.id');
        $ent = D('Addons://SmsBatchSend/SmsLogH')->getOne($id);

        if(!$ent){
            $this->error('该短信不存在');
        }
        $success_num = D('Addons://SmsBatchSend/SmsLogD')->getSuccessNum($id);
        $fail_num = D('Addons://SmsBatchSend/SmsLogD')->getFailNum($id);
        $unsend_num = D('Addons://SmsBatchSend/SmsLogD')->getUnsendNum($id);
        $total = D('Addons://SmsBatchSend/SmsLogD')->where('hid=' . $id)->count();
        $d_ents = D('Addons://SmsBatchSend/SmsLogD')->where('hid=' . $id)->select();
        $success_rate = ($total)>0? (float)$success_num / ($total) * 100 : 0;
        $fail_rate = ($total)>0? (float)$fail_num / ($total) * 100 : 0;
        
        $this->assign('d_list', $d_ents);
        
        $this->assign('success_rate', $success_rate);
        $this->assign('fail_rate', $fail_rate);
        $this->assign('success_num', $success_num);
        $this->assign('fail_num', $fail_num);
        $this->assign('unsend_num', $unsend_num);
        $this->assign('total', $total);
        $this->assign('vo', $ent);
        $log_id = M('SmsMenuConfig')->where(array('kname' => 'log_id'))->getField('value');
        $this->assign('log_id', $log_id);
        $this->display(addon_t('SmsBatchSend', 'viewLog'));
    }
    
    public function changeMobileOfDetail(){
        $d_id = I('post.pk');
        $sms_log_d_model = D('Addons://SmsBatchSend/SmsLogD');
        
        $ent = $sms_log_d_model->getOne($d_id);
        
        if(!$ent){
            $this->error('不存在短信记录');
        }
        
        $mobile = I('post.value');
        
        if(!preg_match('/^1[3|4|5|8]\d{9}$/', $mobile)){
            $this->error('手机格式不正确');
        }
        
        $ent['mobile'] = $mobile;
        
        if($sms_log_d_model->createSave($ent) === false){
            $this->error($sms_log_d_model->getError());
        }
        else{
            $this->success('修改成功');
        }
    }
    
    public function resend(){
        $d_id = I('get.d_id');
        $ent = D('Addons://SmsBatchSend/SmsLogD')->getOne($d_id);
        
        if(!$ent){
            $this->error('不存在短信记录');
        }
        
        $ent_h = D('Addons://SmsBatchSend/SmsLogH')->getOne($ent['hid']);
        
        if(!$ent_h){
            $this->error('不存在短信记录');
        }

        $body = $ent_h['body'];
        $args['telephone'] = $ent['mobile'];
        $body = \Admin\Lib\MsgTemplateParse::parse($args, showHtmlContent($body));
        
        $para['mobile'] = $ent['mobile'];
        $para['content'] = $body;
        \Think\Hook::listen('sendSmsOnce', $para);
        
        foreach($para['return']['data'] as $k=>$v){
            $sid = $v['msgid'];
            $ent['status'] = '等待发送';
            $ent['sid'] = $sid;
            $ent['send_date'] = time();
            
            D('Addons://SmsBatchSend/SmsLogD')->save($ent);
        }
        if($para['return']['status'] == 1){
            $this->success('短信已重发');
        }
        else{
            $this->error($para['return']['err_msg']);
        }
    }
}
