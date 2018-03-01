<?php
namespace Gy_Library;
use Gy_Library\DBCont;

class GyListModel extends GyModel implements IPageModel, ICUDModel, IForbidModel{

    protected $model_name;
    
    protected $_forbid_validate   =   array();    //禁用数据前的验证条件设置
    
    public function getListForCount($map = []){
//        show_bug($this->where($map)->buildSql());
//        exit();
    	if($map){
            $this->where($map);
        }
        return $this->where($map)->count();
    }
    
    public function getListForPage($map, $page, $page_nums, $order = ''){
        if($order != ''){
            $this->order($order);
        }
        //show_bug_ajax($this->where($map)->page($page, $page_nums)->buildSql());
//        show_bug($this->where($map)->page($page, $page_nums)->buildSql());
//        exit();
        return $this->where($map)->page($page, $page_nums)->select();
    }
    
    protected function _forbid_before($id){
        if(!empty($this->_forbid_validate)){
            
            $pk = $this->getPk();
            if(is_string($id)){
                $ids = explode(',', $id);
            }
            else{
                $ids = $id;
            }
            foreach($this->_forbid_validate as $v){
                //设置默认值
                if(!isset($v[2])){
                    $v[2] = self::EXIST_VALIDATE;
                }
                
                if(!isset($v[3])){
                    $v[3] = 'forbid error';
                }
                
                
                switch ($v[2]){
                    case self::EXIST_VALIDATE:
                        $data = $this->_check_exists($v[0], $v[1], $ids);
                        if($data === false){
                            return false;
                        }
                        if($data){
                            $this->error = $v[3];
                            return false;
                        }
                        break;
                    case self::NOT_EXIST_VALIDATE:
                        $data = $this->_check_exists($v[0], $v[1], $ids);
                        if($data === false){
                            return false;
                        }
                        if(!$data){
                            $this->error = $v[3];
                            return false;
                        }
                        break;
                    case self::NOT_ALLOW_VALUE_VALIDATE:
                        $map[$v[1]] = array('in', $v[0]);
                        $na_ids = $this->where($map)->getField($pk, true);
                        
                        $ins_ids = array_intersect($ids,$na_ids);
                        if($ins_ids){
                            $this->error = $v[3];
                            return false;
                        }
                        break;
                }
                
            }
        }
        return true;
    }

    public function forbid($id){
        
        if(false === $this->_forbid_before($id)){
            return false;
        }
        
        $pk = $this->getPk();
        if(is_string($id)){
            $map[$pk] = array('in', explode(',', $id));
        }
        else if(is_array($id)){
            $map[$pk] = array('in', $id);
        }
        $r = $this->where($map)->setField('status', DBCont::FORBIDDEN_STATUS);
        return $r;
    }
    
    public function resume($id){
        $pk = $this->getPk();
         if(is_string($id)){
            $map[$pk] = array('in', explode(',', $id));
        }
        else if(is_array($id)){
            $map[$pk] = array('in', $id);
        }
        $r = $this->where($map)->setField('status', DBCont::NORMAL_STATUS);
        return $r;
    }
    
    public function add($data = '', $options = array(), $replace = false) {
        $r = parent::add($data, $options, $replace);
        return $r;
    }
    
    public function edit($data = '', $options = array(), $msg = '') {
        $r = parent::save($data, $options);
        return $r;
    }
    
    public function del($id = ''){
        $pk = $this->getPk();
         if(is_string($id)){
            $map[$pk] = array('in', explode(',', $id));
        }
        else if(is_array($id)){
            $map[$pk] = array('in', $id);
        }
        $this->where($map);
        $this->startTrans();
        try{
            $r = parent::delete();
            if($r === false){
                E($this->getError());
            }
            
            $this->commit();
            return $r;
        } catch(\Think\Exception $ex){
            $this->rollback();
            $this->error = $ex->getMessage();
            return false;
        }
    }
}
