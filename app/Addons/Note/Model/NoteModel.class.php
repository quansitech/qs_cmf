<?php
namespace Addons\Note\Model;

class NoteModel extends \Gy_Library\GyListModel{
    
    protected $_auto = array(
        array('create_date', "time", parent::MODEL_INSERT, 'function'),
    );
    
    public function getUnreadNum($uid, $type = ''){
        $map['to_uid'] = $uid;
        $map['read'] = 0;
        
        if($type != ''){
            $map['type'] = $type;
        }
        
        return $this->where($map)->count();
    }
    
    public function getUserNote($id, $uid){
        $map['id'] = $id;
        $map['to_uid'] = $uid;
        return $this->where($map)->find();
    }
    
    public function readNote($id, $uid){
        $map['id'] = $id;
        $map['to_uid'] = $uid;
        return $this->where($map)->setField('read', 1);
    }
}
