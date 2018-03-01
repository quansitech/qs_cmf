<?php
namespace Addons\Message\Controller;
use Home\Controller\AddonsController;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MessageController
 *
 * @author tider
 */
class MessageController extends AddonsController {
    //put your code here
    
    public function __construct() {
        parent::__construct();
        
        $this->_admin_function_list = array(
            'index',
            'forbid'
        );
    }
    
    public function index(){
        $map['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $count = D('Addons://Message/Message')->getListForCount($map);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }
        $data_list = D('Addons://Message/Message')->getListForPage($map, $page->nowPage, $page->listRows, 'create_date desc');
        
        $builder = new \Common\Builder\ListBuilder();
        
        $builder->setMetaTitle('评论列表')
                ->addTopButton('forbid', array('title' => '删除', 'href' => addons_url('Message://Message/forbid')))
                ->setNID('message_addon')
                ->addTableColumn('content', '评论内容', 'fun', "D('Addons://Message/Message')->getContentForList(__id__)")
                ->addTableColumn('uid', '评论人', 'fun', 'D("User")->getOneField(__data_id__,"nick_name")')
                ->addTableColumn('rely_id', '所属分类', 'fun', "D('Addons://Message/Message')->getRelyCate(__id__)")
                ->addTableColumn('rely_id', '文章标题', 'fun', "D('Addons://Message/Message')->getRelyTitle(__id__)")
                ->addTableColumn('create_date', '发布时间', 'fun', 'date("Y-m-d H:i:s",__data_id__)')
                ->addTableColumn('right_button', '操作', 'btn')
                ->setTableDataPage($page->show())
                ->setTableDataList($data_list)
                ->addRightButton('self', array('title' => '回复', 'href' => '#','class' => 'label label-primary rely-btn', 'data-id' => '__data_id__', '{key}' => 'uid', '{condition}' => 'neq', '{value}' => session(C('USER_AUTH_KEY'))))
                ->addRightButton('self', array('title' => '回复', 'href' => '#', 'class' => 'label label-default', '{key}' => 'uid', '{condition}' => 'eq', '{value}' => session(C('USER_AUTH_KEY'))))
                ->addRightButton('self', array('title' => '查看', 'href' => addons_url('Message://Message/browse', array('id' => '__data_id__')), 'class' => 'label label-primary','target'=>'_blank'))
                ->addRightButton('self', array('title' => '删除', 'href' => addons_url('Message://Message/forbid', array('ids' => '__data_id__')), 'class' => 'label label-warning ajax-get confirm'))
                ->setExtraHtml($this->fetch(T('Addons://Message@default/message_list')))
                ->display();
    }
    
    public function forbid(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要删除的数据');
        }
        $model = D('Addons://Message/Message');
        $r = $model->forbid($ids);
        if($r === false){
            $this->error($model->getError());
        }
        else{
            $this->success('删除成功');
        }
    }
    
    public function browse(){
        $id = I('get.id');
        if(!$id){
            E('缺少参数:id');
        }
        $ent = D('Addons://Message/Message')->getOne($id);
        $ents = D('Addons://Message/Message')->getMessageListForPage($ent['rely_id'], $ent['rely_key'], '', $count, true, $id);
        if($ents){

            $url = str_replace('__page__', $ents[0]['page'], $ent['url']);

            redirect($url . '#post'.$id);
        }
    }
    
    
    public function comment(){
        needHomeLogin();

        if(IS_POST){
            parent::autoCheckToken();
            $data = I('post.');
            $message_model = D('Addons://Message/Message');
            $time_stamp = time();
            $last_rely_time = $message_model->getLastRelyTime(C('USER_AUTH_KEY'));
            if($time_stamp - $last_rely_time <= 5){
                $this->error('评论不能太频繁');
            }
            
            $addon = new \Addons\Message\MessageAddon();
            $config = $addon->getConfig();
            
            //开启的匿名评论功能时，检测是否匿名评论
            if($config['anon'] == 1 && $data['anon'] == 'on'){
                $data['anon'] = 1;
            }
            else{
                $data['anon'] = 0;
            }
            
            $data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
            $data['uid'] = session(C('USER_AUTH_KEY'));
            $data['create_date'] = time();
            $data['url'] = session('message_url');
            
             if($data['rely_message']){
                $message_ent = $message_model->getMessage($data['rely_message']);
                if(empty($message_ent)){
                    $this->error('回复的留言无效');
                }
                
                if($data['uid'] == $message_ent['uid']){
                    $this->error('不能回复自己的评论');
                }
                
                $data['rely_id'] = $message_ent['rely_id'];
                $data['rely_key'] = $message_ent['rely_key'];
                $data['url'] = $message_ent['url'];
            }
            
            //$para = array($data['content']);
            //\Think\Hook::listen('beforeComment', $para);
            
            //$data['content'] = $para[0];
            
            
            $mid = $message_model->createAdd($data);
            if($mid === false){
                $this->error($message_model->getError());
            }
            
            //回复消息提醒
            if($data['rely_message'] && D('Addons')->where('name="Note" and status=1')->find()){
                
                $message_ent = $message_model->getOne($data['rely_message']);
                $user_ent = D('User')->getOne($data['uid']);
                $rely_message_uid = $message_ent['uid'];
                $param[0] = $data['uid'];
                $param[1] = $rely_message_uid;
                $param[2] = ($data['anon'] == 1 ? '匿名用户' : $user_ent['nick_name']) . ' 回复了您的评论。';
                $param[3] = \Addons\Note\NoteCont::NOTE_TYPE_RELY;
                $param[4] = $mid;
                
                \Think\Hook::listen('makeNote', $param);
            }

            $this->success('发布评论成功！');
        }
    }
    
    public function getMessageNum(){
        $rely_id = I('get.rely_id');
        $rely_key = I('get.rely_key');
        
        echo D("Addons://Message/Message")->getMessageNum($rely_id, $rely_key);
    }
}
