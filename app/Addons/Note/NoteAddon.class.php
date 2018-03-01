<?php
namespace Addons\Note;
use Addons\Addon;

/**
 * Description of NodeAddon
 *
 * @author tider
 */
class NoteAddon extends Addon{
    //put your code here
    
    public $info = array(
        'name' => 'Note',
        'title' => '消息提醒',
        'description' => '系统消息提醒，评论回复提醒，用户短消息发送',
        'status' => 1,
        'author' => 'tider',
        'version' => '0.2'
    );
    
    public function install(){
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}note;");
        $model->execute("CREATE TABLE {$prefix}note (`id` int(11) NOT NULL AUTO_INCREMENT primary key,`from_uid` int(11) NOT NULL,`to_uid` int(11) NOT NULL,`read` tinyint(4) NOT NULL,`title` varchar(200) NOT NULL,`content` text NOT NULL,`create_date` int(11) NOT NULL,`type` tinyint(4) NOT NULL,`message_id` int(11) NOT NULL);");
        
        $this->createHook('makeNote', '创建用户消息');
        $this->createHook('userNote', '用户消息界面');
        $this->createHook('unreadNoteNum', '读取用户未读消息数');
        $this->createHook('readNote', '消息已读标记');
        return true;
    }
    
    public function uninstall(){
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}note;");
        
        $this->delHook('makeNote');
        $this->delHook('userNote');
        $this->deleteHook('unreadNoteNum');
        $this->deleteHook('readNote');
        return true;
    }
    
    public function unreadNoteNum(&$param){

        $uid = $param['uid'];
        $num = D('Addons://Note/Note')->getUnreadNum($uid);
        $param['unread_num'] = $num;
    }
    
    public function makeNote($param){
        $data['from_uid'] = $param['from_uid'];
        $data['to_uid'] = $param['to_uid'];
        $data['read'] = 0;
        $data['content'] = $param['content'];
        $data['type'] = $param['type'];
        $data['message_id'] = $param['message_id'];
        $data['title'] = $param['title'];
        
        D('Addons://Note/Note')->createAdd($data);
    }
    
    public function userNote(&$param){
        $map = $param['map'];
        $order = $param['order'];
        $page = $param['page'];
        $page_num = $param['page_num'];
        
        $list = D('Addons://Note/Note')->getListForPage($map, $page, $page_num, $order);
        $count = D('Addons://Note/Note')->getListForCount($map);
        $param['list'] = $list;
        $param['count'] = $count;
    }
    
    public function readNote(&$param){
        $id = $param['id'];
        $uid = $param['uid'];
        
        $param['result'] = D('Addons://Note/Note')->readNote($id, $uid);
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
