<?php

namespace Common\Model;
use Qscmf\Lib\DBCont;

class MenuModel extends \Gy_Library\GyListModel{
    
    protected $model_name = '菜单';
    
    protected $_validate = array(
        array('title','require', '{%TITLE_REQUIRED}'),
        array('status','require', '{%STATUS_REQUIRED}'),
        array('sort','require', '{%SORT_REQUIRED}'),
        array('type','require', '{%TYPE_REQUIRED}'),
        array('status',array(DBCont::FORBIDDEN_STATUS, DBCont::NORMAL_STATUS),'{%STATUS_OUT_OF_RANGE}',parent::MUST_VALIDATE, in, parent::MODEL_BOTH),
    );
    
    protected $_delete_validate = array(
        array('Node', 'menu_id', parent::EXIST_VALIDATE , '请先清空菜单所关联的节点'),
    );
    
    
    public function getMenuList( $type = '', $pid = '', $order = 'type asc, sort asc'){
        $map['status'] = DBCont::NORMAL_STATUS;
        if($type != ''){
            $map['type'] = $type;
        }
        if($pid != ''){
            $map['pid'] = $pid;
        }
        return $this->where($map)->order($order)->select();
    }
    
    //获取以菜单类型为键值的菜单数组
    public function getMenuListGroupByType(){
        $menu_list = $this->getMenuList();
        $r = array();
        foreach ($menu_list as $v){
            $r[$v['type']][] = array('id'=>$v['id'], 'title' => $v['title']);
        }
        return $r;
    }
    
    public function getMenuTitle($menu_id){
        $ent = $this->getOne($menu_id);
        return $ent['title'];
    }
    
}
