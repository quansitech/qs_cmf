<?php
namespace Common\Model;
use Qscmf\Lib\DBCont;

class NodeModel extends \Gy_Library\GyListModel{
    
    protected $model_name = '节点';
    
    protected $_validate = array(
        array('name', 'require', '名称是必填项'),
        array('title', 'require', '标题是必填项'),
        array('sort','/^[0-9]+$/','{%MUST_BE_INTEGER}',parent::VALUE_VALIDATE,'regex'),
        array('level', 'require', 'level是必填项'),
        array('level',array(DBCont::LEVEL_MODULE,DBCont::LEVEL_CONTROLLER,DBCont::LEVEL_ACTION),'level值不规范',parent::MUST_VALIDATE, in, parent::MODEL_BOTH),
        array('pid', '/^[0-9]+$/','{%MUST_BE_INTEGER}',parent::MUST_VALIDATE,'regex'),
        array('status',array(DBCont::FORBIDDEN_STATUS, DBCont::NORMAL_STATUS),'{%STATUS_OUT_OF_RANGE}',parent::MUST_VALIDATE, in, parent::MODEL_BOTH),
    );
    
//    protected $_delete_auto = array(
//        array('Access', 'node_id'),
//    );
    
    protected $_delete_validate = array(
        array('Access', 'node_id', parent::EXIST_VALIDATE , '请先将该节点从用户组剔除'),
    );
    
    public function getNode($map){
        return $this->where($map)->find();
    }
    
    public function getNodeList($map){
        return $this->where($map)->order('sort asc')->select();
    }
    
    public function getModuleList(){
        $map['level'] = DBCont::LEVEL_MODULE;
        $map['status'] = DBCont::NORMAL_STATUS;
        
        return $this->getList($map);
    }
    
    public function isExistsNode($module, $controller, $node){
        $map['name'] = $module;
        $map['status'] = DBCont::NORMAL_STATUS;
        $map['level'] = DBCont::LEVEL_MODULE;
        
        $module_ent = $this->getNode($map);
        
        if(!$module_ent){
            return false;
        }
        
        $c_map['name'] = $controller;
        $c_map['status'] = DBCont::NORMAL_STATUS;
        $c_map['level'] = DBCont::LEVEL_CONTROLLER;
        $c_map['pid'] = $module_ent['id'];
        
        $controller_ent = $this->getNode($c_map);
        
        if(!$controller_ent){
            return false;
        }
        
        $n_map['name'] = $node;
        $n_map['status'] = DBCont::NORMAL_STATUS;
        $n_map['level'] = DBCont::LEVEL_ACTION;
        $n_map['pid'] = $controller_ent['id'];
        
        $node_ent = $this->getNode($n_map);
        
        if(!$node_ent){
            return false;
        }
        return true;
    }
    
    public function getNodeName($module, $controller, $node){
        $map['name'] = $module;
        $map['level'] = DBCont::LEVEL_MODULE;
        $module_ent = $this->getNode($map);
        
        if(!$module_ent){
            return '';
        }
        
        $c_map['name'] = $controller;
        $c_map['level'] = DBCont::LEVEL_CONTROLLER;
        $c_map['pid'] = $module_ent['id'];
        
        $controller_ent = $this->getNode($c_map);
        
        if(!$controller_ent){
            return '';
        }
        
        $n_map['name'] = $node;
        $n_map['level'] = DBCont::LEVEL_ACTION;
        $n_map['pid'] = $controller_ent['id'];
        
        $node_ent = $this->getNode($n_map);
        
        if(!$node_ent){
            return '';
        }
        return $node_ent['title'];
    }
}
