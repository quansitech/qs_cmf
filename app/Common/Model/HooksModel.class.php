<?php

namespace Common\Model;
use Qscmf\Lib\DBCont;

class HooksModel extends \Gy_Library\GyListModel{
    protected $_validate = array(
        array('name', 'require', '名称是必填项'),
        array('status',array(DBCont::FORBIDDEN_STATUS, DBCont::NORMAL_STATUS),'{%STATUS_OUT_OF_RANGE}',parent::MUST_VALIDATE, in, parent::MODEL_BOTH),
    );
    
    protected $_auto = array(
        array('update_date', "time", parent::MODEL_BOTH, 'function'),
        array('status', DBCont::NORMAL_STATUS, self::MODEL_INSERT)
    );
    
    public function __construct($name = '', $tablePrefix = '', $connection = '') {
        parent::__construct($name, $tablePrefix, $connection);
        
//        $ids = M('Hooks')->where("score_rule=''")->getField('id', true);
//        $this->_delete_validate[] = array($ids, 'id', parent::ALLOW_VALUE_VALIDATE, '只能删除没有挂载规则的钩子');
    }
    
    public function getHooks($map){
        return $this->where($map)->select();
    }
    
    public function getActiveHooks(){
        $map = array('status' => DBCont::NORMAL_STATUS);
        return $this->getHooks($map);
    }
}
