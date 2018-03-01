<?php
namespace Addons\Donate\Controller;
use Home\Controller\AddonsController;
use \Addons\Donate\DonateCont;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DonateController
 *
 * @author tider
 */
class DonateController extends AddonsController {
    
    public function __construct() {
        parent::__construct();
        
        $this->_admin_function_list = array('import', 'add');
    }
    
    public function index(){
        $id = I('get.id');
        $t = I('get.t');
        if(empty($t) || empty($id)){
            E('缺少参数');
        }
        
        if(IS_POST){
            $this->dbname = 'DonateHeader';
            parent::autoCheckToken();
            
            $mail_type = I('post.mail_type');
            if($mail_type == ''){
                $this->error('请填写反馈信息', U('home/donate/index', array('id'=>$id, 't'=>$t)));
            }
            
            if(I('post.donate_amount') == 'other'){
                $donate_amount = I('post.other_amount');
            }
            else{
                $donate_amount = I('post.donate_amount');
            }
            $donate_amount = (float)$donate_amount;
            
 //           $detail_data = array();
//            foreach($donate_detail as $v){
//                $tmp_data['amount'] = $v['sum'];
//                $tmp_data['ref_id'] = $v['id'];
//                $tmp_data['ref_key'] = $v['type'];
//                
//                $detail_data[] = $tmp_data;
//            }
            

            $t_ent = D(ucfirst($t))->where(array('status' => \Gy_Library\DBCont::NORMAL_STATUS, 'id' => $id))->find();
            if(!$t_ent){
                E('无效捐赠项');
            }
            $tmp['amount'] = $donate_amount;
            $tmp['ref_id'] = $id;
            $tmp['ref_key'] = $t;
            $detail_data[] = $tmp;
            
            $this->_checkInput($mail_type);
            
            $data = I('post.');
            $data['uid'] = isLogin() ? session(C('USER_AUTH_KEY')) : 0;
            $data['status'] = \Addons\Donate\DonateCont::DONATE_STATUS_NOPAY;
            $data['channel_id'] = \Addons\Donate\DonateCont::PAY_CHANNEL_ALIPAY;
            //$data['donator_info'] = json_encode($donator_info);
            
            $data['name'] = I('post.name');
            $data['prefix'] = I('post.prefix');
            $data['email'] = I('post.email');
            $data['telephone'] = I('post.telephone');
            $data['address'] = I('post.address');
            
            
            $donate_header_model = D('Addons://Donate/DonateHeader');
            $header_id = $donate_header_model->donate($data, $detail_data);
            
            if($header_id === false){
                $this->error($donate_header_model->getError(), U('home/donate/index', array('id'=>$id, 't'=>$t)));
            }
            
            //清除cookies
            //cookie('donate', null);
            
            $alipay = new \Addons\Donate\Util\Alipay();
            $addon = new \Addons\Donate\DonateAddon();
            $config = $addon->getConfig();
            $out_trade_no = $config['alipay_trade_no_prefix'] . $header_id;
            $donate_header_ent = $donate_header_model->getOne($header_id);
            
            $extra_para = array('to_mail' => I('post.email'), 'name' => I('post.name'));
            $subject = '捐赠_' . $t_ent['title'];
            $body = '捐赠_' . $t_ent['title'];

            $alipay->submit($out_trade_no, $subject, $body, $donate_header_ent['total_amount'], json_encode($extra_para));
        }
    }
    
    private function _checkInput($mail_type){
        switch($mail_type){
            case \Addons\Donate\DonateCont::DONATE_MAIL_TYPE_EMAIL:
                $name = I('post.name');
                $email = I('post.email');
                $bill_title = I('post.bill_title');
                if(empty($email) || empty($bill_title) || empty($name)){
                    $this->error('请填写电子邮箱、收据抬头', U('home/donate/index', array('id'=>I('get.id'), 't'=>I('get.t'))));
                }
                break;
            case \Addons\Donate\DonateCont::DONATE_MAIL_TYPE_EMS:
                $name = I('post.name');
                $telephone = I('post.telephone');
                $address = I('post.address');
                $bill_title = I('post.bill_title');
                if(empty($name) || empty($telephone) || empty($address) || empty($bill_title)){
                    $this->error('请填写姓名、手机号码、地址、收据抬头', U('home/donate/index', array('id'=>I('get.id'), 't'=>I('get.t'))));
                }
                break;
            case \Addons\Donate\DonateCont::DONATE_MAIL_TYPE_SELF:
                $name = I('post.name');
                $telephone = I('post.telephone');
                $bill_title = I('post.bill_title');
                if(empty($name) || empty($telephone) || empty($bill_title)){
                    $this->error('请填写姓名、手机号码、收据抬头', U('home/donate/index', array('id'=>I('get.id'), 't'=>I('get.t'))));
                }
                break;
            default:
                break;
        }
    }
    
    public function donate(){
        needHomeLogin();
        
        $id = I('get.id');
        
        $ent = D('Addons://Donate/DonateHeader')->getOne($id);
        $detail_ent = D('Addons://Donate/DonateDetail')->where(array('header_id' => $ent['id']))->find();
        $t_ent = D(ucfirst($detail_ent['ref_key']))->where(array('status' => \Gy_Library\DBCont::NORMAL_STATUS, 'id' => $detail_ent['ref_id']))->find();
        if(!$ent || $ent['status'] != DonateCont::DONATE_STATUS_NOPAY || $ent['uid'] != session(C('USER_AUTH_KEY'))){
            $this->error('无效捐赠id');
        }
        
        //$donator_info = json_decode($ent['donator_info'], true);
        $extra_para = array('to_mail' => $ent['email'], 'name' => $ent['name']);
        $alipay = new \Addons\Donate\Util\Alipay();
        $addon = new \Addons\Donate\DonateAddon();
        $config = $addon->getConfig();
        $out_trade_no = $config['alipay_trade_no_prefix'] . $id;
        $subject = '捐赠_' . $t_ent['title'];
        $body = '捐赠_' . $t_ent['title'];
        $alipay->submit($out_trade_no, $subject, $body, $ent['total_amount'], json_encode($extra_para));
    }
    
    public function donateCancel(){
        needHomeLogin();
        $id = I('get.id');
        $uid = session(C('USER_AUTH_KEY'));
        $donate_header_model = D('Addons://Donate/DonateHeader');
        $donate_detail_model = D('Addons://Donate/DonateDetail');
        $ent = $donate_header_model->getOne($id);
        if(!$ent || $ent['status'] != DonateCont::DONATE_STATUS_NOPAY || $ent['uid'] != $uid){
            $this->error('无效捐赠id');
        }
        
        $r = $donate_header_model->where('id=' . $ent['id'])->delete();
        $donate_detail_model->where('header_id='.$ent['id'])->delete();
        if($r === false){
            $this->error('取消捐赠订单出错');
        }
        else{
            $this->success('已成功取消');
        }
    }
    
    public function notify(){
        $r = $this->_handle($msg, $status, 'notify');
        if($r){
            echo 'success';
        }
        else{
            echo 'fail';
        }
    }
    
    public function retn(){
        $r = $this->_handle($msg, $status, 'return');
        $this->redirect('Home/Support/donationResult', array('msg' => $msg, 'status' => $status));
    }
    
    private  function _handle(&$msg, &$status, $type){
        $alipay = new \Addons\Donate\Util\Alipay();
        $config = $alipay->getConfig();
        
        $verify_result = $alipay->verify($type);
        
        if($verify_result) {
            
            //商户订单号
            $out_trade_no = I('out_trade_no');
            
            $donate_id = str_replace($config['alipay_trade_no_prefix'], '', $out_trade_no);
            
            $buyer_info['buyer_email'] = I('buyer_email');
            $buyer_info['buyer_id'] = I('buyer_id');
            $buyer_info['out_trade_no'] = I('out_trade_no');
            $buyer_info['trade_no'] = I('trade_no');
            
            $extra_common_param = I('extra_common_param');
            
            $extra_arr = json_decode(html_entity_decode($extra_common_param), true);

            //交易状态
            $trade_status = I('trade_status');
            
            $donate_header_model = D('Addons://Donate/DonateHeader');

            if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                $r = $donate_header_model->alipayDonate($donate_id, $buyer_info);
                if($r === false){
                    $msg = $donate_header_model->getError();
                    $status = 0;
                    return false;
                }
                else{
//                    if(!empty($extra_arr['to_mail'])){
//                        $mail = new \Gy_Library\GyMail();
//                        $mail->sendThanksMail($extra_arr['to_mail'], $extra_arr['name']);
//                    }
                    $msg = '捐赠成功';
                    $status = 1;
                    return true;
                }
            }
            else {
              $msg = "捐赠失败";
              $status = 0;
              return false;
            }
        }
        else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            $msg = "验证失败";
            $status = 0;
            return false;
        }
    }
    
    public function exportTemplate(){
        $donate_import_widget = new \Addons\Donate\Widget\DonateImportWidget();
        $excel_header = array();
        foreach($donate_import_widget->columns_setting as $v){
            $excel_header[] = $v['column_name'];
        }
        
        $donate_type = '单次捐赠,活动捐赠';
        $donate_project = '';
        foreach(readDBDataList('DonateProject', 'status=1', 'sort asc') as $v){
            $donate_project .= $v['title'] . ',';
        }
        $donate_project = trim($donate_project, ',');
        $dactivity = '';
        foreach(importAbleDactivity() as $v){
            $dactivity .= $v['title'] . ',';
        }
        $dactivity = trim($dactivity, ',');
        
        $donate_title = $donate_project . ',' . $dactivity;
        
        $channel = '';
        foreach(DonateCont::getPayChannelList() as $v){
            $channel .= $v . ',';
        }
        $channel = trim($channel, ',');
        
        $excel_list[] = $excel_header;
        $excel_list[] = array('2015-09-16','测试人员1','100','list:单次捐赠@' . $donate_type,'list:机构发展专项基金@' . $donate_title,'list:现金@' . $channel);
        for($i = 0; $i<500;$i++){
            $excel_list[] = array('','','','list:@' . $donate_type,'list:@' . $donate_title,'list:@' . $channel);
        }
        
        $gy_excel = new \Gy_Library\GyExcel(20,20);
        if($gy_excel->exportToDownload($excel_list, 'donate_import.xls') === false){
            $this->error($gy_excel->getError());
        }
        
    }
    
    public function import(){
        if(IS_POST){
            $upload = new \Think\Upload(C('UPLOAD_TYPE_FILE'));
            $info = $upload->upload($_FILES);
            if (!$info) {
                $this->error($upload->getError());
            }else{
                $file_path = UPLOAD_DIR . DIRECTORY_SEPARATOR . $info['file']['savepath'] . $info['file']['savename'];
                $this->assign('import_file', $file_path);
            }
        }
        $this->display(T("Addons://Donate@default/import"));
    }
    
    public function delete(){
        $id = I('get.id');
        $header_model = D('Addons://Donate/DonateHeader');
        $header_ent = $header_model->getOne($id);
        if(!$header_ent){
            E('数据不存在');
        }
        
        if($header_ent['offline'] != 1){
            E('非线下导入数据，不能删除');
        }
        
        $r = $header_model->del($id);
        if($r === false){
            $this->error($header_model->getError());
        }
        else{
            $this->success('删除成功');
        }
    }
    
    public function add(){
        if(IS_POST){
            parent::autoCheckToken();
            
            $data = I('post.');
            
            $error_arr = array();
            $header_data = array();
            $detail_data = array();
            foreach($data['donate_date'] as $k => $v){
                $donate_date = strtotime($v);
                if($donate_date === false){
                    $error_arr[] = array('error_id' => $k. '_0', 'error_msg' => '捐赠日期格式不正确');
                }
                
                $total_amount = round($data['total_amount'][$k],2);
                if(is_float(floatval($total_amount)) === false){
                    $error_arr[] = array('error_id' => $k . '_2', 'error_msg' => '金额不是有效数字');
                }
                
                if($total_amount == 0){
                    $error_arr[] = array('error_id' => $k . '_2', 'error_msg' => '金额不能为0');
                }
                
                $ref_key = $data['ref_key'][$k];
                $ref_id = $data['ref_id'][$k];
                if($ref_key != 'donateActivity' && $ref_key != 'donateProject'){
                    $error_arr[] = array('error_id' => $k . '_3', 'error_msg' => '捐赠类型不正确');
                }
                else{
                    if($ref_key == 'donateProject' && D('DonateProject')->isDpurpose($ref_id) === false){
                        $error_arr[] = array('error_id' => $k . '_4', 'error_msg' => '类型关联无效');
                    }

                    if($ref_key == 'donateActivity' && D('DonateActivity')->isImportAble($ref_id) === false){
                        $error_arr[] = array('error_id' => $k . '_4', 'error_msg' => '类型关联无效');
                    }
                }
                
                $channel_id = $data['channel_id'][$k];
                if(!DonateCont::getPayChannel($channel_id)){
                    $error_arr[] = array('error_id' => $k . '_5', 'error_msg' => '捐赠渠道无效');
                }
                
                $header_data[] = array('uid' => 0, 'donate_date' => $donate_date, 'status' => DonateCont::DONATE_STATUS_PAID, 'total_amount' => $total_amount, 'channel_id' => $channel_id, 'mail_type' => DonateCont::DONATE_MAIL_TYPE_NONEED, 'name' => $data['name'][$k], 'secret' => DonateCont::PUBLISH_DOESNT_MATTER, 'offline' => 1, 'remark' => $data['remark'][$k]);
                $detail_data[] = array('line_no' => 1, 'amount' => $total_amount, 'ref_id' => $ref_id, 'ref_key' => $ref_key);
            }
            
            if(count($error_arr) > 0){
                $ajax_return_data = array('status' => 0, 'data' => $error_arr);
                $this->ajaxReturn($ajax_return_data);
            }
            
            $header_model = D('Addons://Donate/DonateHeader');
            if($header_model->addBatchByExcel($header_data, $detail_data) === false){
                $ajax_return_data = array('status' => 0, 'msg' => $header_model->getError());
                $this->ajaxReturn($ajax_return_data);
            }
            
            $ajax_return_data = array('status' => 1, 'msg' => '导入成功', 'url' => addons_url('Donate://Donate/import'));
            $this->ajaxReturn($ajax_return_data);
        }
    }
}
