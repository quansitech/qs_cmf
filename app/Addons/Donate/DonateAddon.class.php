<?php
namespace Addons\Donate;
use Addons\Addon;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DonateAddon
 *
 * @author tider
 */
class DonateAddon extends Addon{
    public $info = array(
        'name' => 'Donate',
        'title' => '公益捐赠',
        'description' => '标准公益捐赠模块',
        'status' => 1,
        'author' => 'tider',
        'version' => '0.1'
    );
    
    public function install(){
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}donate_header;");
        $model->execute("CREATE TABLE {$prefix}donate_header (`id` int(11) NOT NULL AUTO_INCREMENT primary key,`uid` int(11) NOT NULL,`donate_date` int(11) NOT NULL,`status` tinyint(4) NOT NULL,`total_amount` decimal(10,2) NOT NULL,`channel_id` tinyint(4) NOT NULL,`mail_type` tinyint(4) NOT NULL,`bill_title` varchar(200) NOT NULL,`buyer_info` varchar(5000) NOT NULL,`secret` tinyint(4) NOT NULL,`remark` varchar(2000) NOT NULL,`name` varchar(100) NOT NULL,`prefix` tinyint(4) NOT NULL,`email` varchar(500) NOT NULL,`telephone` varchar(50) NOT NULL,`address` varchar(500) NOT NULL,`mail_code` varchar(50) NOT NULL,`offline` tinyint(4) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $model->execute("DROP TABLE IF EXISTS {$prefix}donate_detail;");
        $model->execute("CREATE TABLE {$prefix}donate_detail (`id` int(11) NOT NULL AUTO_INCREMENT primary key,`header_id` int(11) NOT NULL,`line_no` tinyint(4) NOT NULL,`amount` decimal(10,2) NOT NULL,`ref_id` int(11) NOT NULL,`ref_key` varchar(50) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        return true;
    }
    
    public function uninstall(){
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}donate_header;");
        $model->execute("DROP TABLE IF EXISTS {$prefix}donate_detail;");
        return true;
    }
    
    public function donate($param){
        
        if(isLogin()){
            $uid = session(C("USER_AUTH_KEY"));
            $user_ent = D('User')->getOne($uid);
            $profile_ent = D('UserProfile')->where('uid=' . $uid)->find();
            if($profile_ent){
                $user_ent['name'] = $profile_ent['real_name'];
                $user_ent['gender'] = $profile_ent['gender'];
            }

            $this->assign('user', $user_ent);
        }
        else{
            if(isShowVerify()){
                $this->assign('verify_show', true);
            }
        }

        $this->display(T('Addons://Donate@default/donate'));
    }
    
    public function donateList($param){
        $type = $param[0];
        
        $donate_date_range = I('get.donate_date_range'); 
        $channel_id = I('get.channel_id');
        $donator = I('get.donator');
        $ref_name = I('get.ref_name');
        
        $donate_header_model = D('Addons://Donate/DonateHeader');
        
        $count = $donate_header_model->donateListCount($donate_date_range, $channel_id, $donator, $ref_name);
        $list_rows = C(strtoupper($type) . '_PER_PAGE_NUM', null, false);
        
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
        
        $donation_list = $donate_header_model->donateList($donate_date_range, $channel_id, $donator, $ref_name, $page->nowPage, $page->listRows);
        foreach($donation_list as $k=>$v){
            if($v['secret'] == DonateCont::PUBLISH_FORBID){
                $v['name'] = maskName($v['name']);
            }
            
            $ref_ent = D(ucfirst($v['ref_key']))->getOne($v['ref_id']);
            $v['ref_title'] = $ref_ent['title'];
            
            $v['channel_name'] = DonateCont::getPayChannel($v['channel_id']);
            
            $donation_list[$k] = $v;
        }
        
        $channel_list = DonateCont::getPayChannelList();
        
        $this->assign('channel_list', $channel_list);
        $this->assign('donation_list', $donation_list);
        $this->display(T("Addons://Donate@default/{$type}_donate_list"));
    }
    
    public function userDonateList($param){
        $uid = $param[0];
        
        $donate_header_model = D('Addons://Donate/DonateHeader');
        $map['uid'] = session(C('User_AUTH_KEY'));
        $count = $donate_header_model->getListForCount($map);
        
        $list_rows = C('HOME_PER_PAGE_NUM', null, false);
        
        $para = array();
        if($list_rows === false){
            $para = array($count);
        }
        else{
            $para = array($count, $list_rows);
        }

        //$class = new GyPage($para);
        $class = new \ReflectionClass('\Gy_Library\GyPage');
        $page = $class->newInstanceArgs($para);

        $pagination = $page->show();
        $this->assign('pagination', $pagination);
        
        $data_list = $donate_header_model->getListForPage($map, $page->nowPage, $page->listRows, 'donate_date desc');
        
        
        foreach ($data_list as $k => $v){
            $v['channel_name'] = DonateCont::getPayChannel($v['channel_id']);
            $v['status_name'] = DonateCont::getDonateStatus($v['status']);
            $v['mail_type_name'] = DonateCont::getDonateMailType($v['mail_type']);
            $v['donator_info'] = $this->_translateDonatorInfo($v);
            $donate_detail = D('Addons://Donate/DonateDetail')->getDetailByHeaderId($v[id]);
            array_walk($donate_detail, array($this, '_translateDonateDetail'));
            $v['donate_detail'] = $donate_detail;
            
            $data_list[$k] = $v;
        }
        
        $this->assign('list', $data_list);
        $this->display(T("Addons://Donate@default/user_donate_list"));
    }
    
    private function _translateDonatorInfo($data){
        $donator_info = array();
        $donator_info['name'] = $data['name'];
        $donator_info['prefix'] = $data['prefix'] == '' ? '' : DonateCont::getPrefix($data['prefix']);
        $donator_info['email'] = $data['email'];
        $donator_info['telephone'] = $data['telephone'];
        $donator_info['address'] = $data['address'];
        $donator_info['mail_code'] = $data['mail_code'];
        return json_encode($donator_info);
    }
    
    protected function _translateDonateDetail(&$value, $key){
        $ref_id = $value['ref_id'];
        $ref_key = $value['ref_key'];
        
        switch($ref_key){
            case 'donateProject':
                $value['key_name'] = '单次捐赠';
                $value['ref_name'] = D(ucfirst($ref_key))->where(array('id' => $ref_id))->getField('title');
                break;
            case 'donateActivity':
                $value['key_name'] = '筹款活动捐赠';
                $value['ref_name'] = D(ucfirst($ref_key))->where(array('id' => $ref_id))->getField('title');
                break;
            default:
                break;
        }
        
        unset($value['id']);
        unset($value['header_id']);
        unset($value['ref_id']);
        unset($value['ref_key']);
    }
}
