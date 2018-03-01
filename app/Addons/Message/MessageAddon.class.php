<?php
namespace Addons\Message;
use Addons\Addon;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MessageAddon
 *
 * @author tider
 */
class MessageAddon extends Addon{
    //put your code here
    
    public $info = array(
        'name' => 'Message',
        'title' => '用户评论',
        'description' => '用户评论功能',
        'status' => 1,
        'author' => 'tider',
        'version' => '0.1'
    );
    
    public function install(){
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}message;");
        $model->execute("CREATE TABLE {$prefix}message (`id` int(11) NOT NULL AUTO_INCREMENT primary key,  `content` text NOT NULL,`rely_id` int(11) NOT NULL,  `rely_key` varchar(50) NOT NULL,  `url` varchar(200) NOT NULL, `rely_message` int(11) NOT NULL, `anon` tinyint(4) NOT NULL, `status` tinyint(4) NOT NULL,  `uid` int(11) NOT NULL,  `create_date` int(11) NOT NULL)");
        
        //增加留言管理的权限点
        $admin_node = D('Node')->where(array('name' => 'admin'))->find();
        $controller_node_ent = array(
            'name' => 'message',
            'title' => '评论管理',
            'status' => \Gy_Library\DBCont::NORMAL_STATUS,
            'pid' => $admin_node['id'],
            'level' => \Gy_Library\DBCont::LEVEL_CONTROLLER
        );
        $controller_id = D('Node')->add($controller_node_ent);
        
        if($controller_id === false){
            return false;
        }
        
        $action_node_ent = array(
            'name' => 'index',
            'title' => '评论列表',
            'status' => \Gy_Library\DBCont::NORMAL_STATUS,
            'pid' => $controller_id,
            'level' => \Gy_Library\DBCont::LEVEL_ACTION
        );
        D('Node')->add($action_node_ent);
        
        $action_node_ent = array(
            'name' => 'forbid',
            'title' => '删除',
            'status' => \Gy_Library\DBCont::NORMAL_STATUS,
            'pid' => $controller_id,
            'level' => \Gy_Library\DBCont::LEVEL_ACTION
        );
        D('Node')->add($action_node_ent);
        
        $action_node_ent = array(
            'name' => 'comment',
            'title' => '回复',
            'status' => \Gy_Library\DBCont::NORMAL_STATUS,
            'pid' => $controller_id,
            'level' => \Gy_Library\DBCont::LEVEL_ACTION
        );
        D('Node')->add($action_node_ent);
        
        return true;
    }
    
    public function uninstall(){
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}message;");
        
        $ent = D('Node')->where(array('name' => 'message', 'level' => \Gy_Library\DBCont::LEVEL_CONTROLLER))->find();
        D('Node')->where(array('pid' => $ent['id']))->delete();
        D('Node')->where(array('id' => $ent['id']))->delete();
        return true;
    }
    
    public function userComment($uid){
        needHomeLogin();
        
        $message_model = D('Addons://Message/Message');
        $map['uid'] = $uid;
        $map['status'] = 1;
        $count = $message_model->getListForCount($map);
        $per_page = C('HOME_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }
        
        $message_ents = $message_model->getListForPage($map, $page->nowPage, $page->listRows, 'create_date desc');
        foreach($message_ents as &$ent){
            $ent['title'] = $message_model->getRelyTitle($ent['id']);
            if($ent['rely_message']){
                $rely_user_nick_name = $message_model->getNickName($ent['rely_message']);
                $ent['content'] = '回复'. $rely_user_nick_name .'的评论:' . $ent['content'];
            }
        }
        $this->assign('pagination', $page->show());
        $this->assign('message_list', $message_ents);
        $this->display(T('Addons://Message@default/user_comment'));
    }
    
    public function adminMenuBefore(&$menu_list){
        $node_list[] = array(
            'id' => 'message_addon',
            'url' => addons_url('Message://Message/index'),
            'title' => '评论列表'
        );
        
        
        $menu_list[] = array(
            'icon' => 'fa-list-alt', 
            'title' => '评论管理', 
            'node_list' => $node_list
       );
    }
    
    public function documentDetailAfter($param){
        $rely_id = $param[0];
        $rely_key = $param[1];
        $url = $param[2];
        $tab_page = $param[3];
        $tab = $param[4];
        
        if(isLogin()){
            $uid = session(C('USER_AUTH_KEY'));
            $user_ent = D('User')->find($uid);
            $user_ent['name'] = getUserName($uid);
            $user_ent['pic_url'] = showFileUrl($user_ent['portrait']);

            $this->assign('user', $user_ent);
            $this->assign('rely_id', $rely_id);
            $this->assign('rely_key', $rely_key);
            
        }
        
        session('message_url', $url);
        
        if(empty($tab_page)){
            $tab_page = 1;
        }
        
        if(strpos($tab_page, ',') !== false){
            $tab_page_arr = $this->_decrypt($tab_page);
            $page = $tab_page_arr[$tab];
        }
        else{
            $page = $tab_page;
        }
        $this->assign('tab_page', $tab_page);
        $this->_getMessageList($rely_id, $rely_key, $page);
        $this->assign('config', $this->getConfig());
        $this->display('message');
        
    }
    
    private function _getMessageList($rely_id, $rely_key, $page){
        $message_list = D('Addons://Message/Message')->getMessageListForPage($rely_id, $rely_key, $page, $count);
        $this->assign('per_page', C('HOME_PER_PAGE_NUM'));
        $this->assign('message_list', $message_list);
        $this->assign('message_count', $count);
    }
    
    private function _decrypt($str){
        $arr = explode(',', $str);

        $return = array();
        foreach($arr as $k => $v){
            $temp_arr = explode('-', $v);
            $return[$temp_arr[0]] = $temp_arr[1];
        }
        return $return;
    }
}
