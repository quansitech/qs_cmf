<?php

namespace Common\Model;
use Gy_Library\GyModel;
use Gy_Library\DBCont;

class AccessModel extends GyModel{
    
    protected $_validate = array(
        array('role_id', 'require', 'role_id是必填项'),
        array('node_id', 'require', 'node_id是必填项'),
        array('level', 'require', 'level是必填项'),
        array('level',array(DBCont::LEVEL_MODULE,DBCont::LEVEL_CONTROLLER,DBCont::LEVEL_ACTION),'level值不规范',parent::MUST_VALIDATE, in, parent::MODEL_BOTH),
    );
    
    public function delAccess($map){
        return $this->where($map)->delete();
    }
    
    public function getAccessList($map){
        return $this->where($map)->select();
    }
}

