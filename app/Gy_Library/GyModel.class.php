<?php

namespace Gy_Library;
use Think\Model;

class GyModel extends Model {
    
    const EXIST_VALIDATE = 1;
    const NOT_EXIST_VALIDATE = 2;
    const NOT_ALLOW_VALUE_VALIDATE = 3;
    const ALLOW_VALUE_VALIDATE = 4;
    const EXIST_TABLE_VALUE_VALIDATE = 5;
    
    protected $_delete_validate   =   array();    //删除数据前的验证条件设置


    //array(array('delete',  'VolunteerExtend', array('uid' => 'uid'))) delete规则  arr[1] 要删除的model名, arr[2] key和value是被删除表与连带删除表的映射字段
    protected $_delete_auto = array();  //删除数据自动执行操作 
    
    public function notOptionsFilter(){
        $this->options['cus_filter'] = false;
        return $this;
    }
    
    public function enableOptionsFilter(){
        unset($this->options['cus_filter']);
        return $this;
    }
    
    public function notBeforeWrite(){
        $this->options['cus_before_write'] = false;
        return $this;
    }
    
    public function enableBeforeWrite(){
        unset($this->options['cus_before_write']);
        return $this;
    }
    
    public function autoCheckToken($data) {
        //ajax无刷新提交，不检验token
        if(IS_AJAX){
            return true;
        }
        
        $result = parent::autoCheckToken($data);
                
        if(C('GY_TOKEN_ON') && C('TOKEN_ON')){
            C('TOKEN_ON', false);
        }
        return $result;
    }
    
    //数据是否可删除
    protected function _before_delete($options){

        if(!empty($this->_delete_validate)){
            
            $pk = $this->getPk();
            $ids = $this->where($options['where'])->getField($pk, true);
            foreach($this->_delete_validate as $v){
                //设置默认值
                if(!isset($v[2])){
                    $v[2] = self::EXIST_VALIDATE;
                }
                
                if(!isset($v[3])){
                    $v[3] = 'delete error';
                }
                

                switch ($v[2]){
                    case self::EXIST_VALIDATE:
                        $data = $this->_checkExists($v[0], $v[1], $ids);
                        
                        if($data === false){
                            return false;
                        }
                        if($data){
                            $this->error = $v[3];
                            return false;
                        }
                        break;
                    case self::NOT_EXIST_VALIDATE:
                        $data = $this->_checkExists($v[0], $v[1], $ids);
                        if($data === false){
                            return false;
                        }
                        if(!$data){
                            $this->error = $v[3];
                            return false;
                        }
                        break;
                    case self::NOT_ALLOW_VALUE_VALIDATE:
                        //获取禁止删除的ID
                        $map[$v[1]] = array('in', $v[0]);
                        $na_ids = $this->where($map)->getField($pk, true);
                        //如存在交集，证明删除的的记录里存在不允许删除的记录
                        $ins_ids = array_intersect($ids,$na_ids);
                        if($ins_ids){
                            $this->error = $v[3];
                            return false;
                        }
                        break;
                    case self::ALLOW_VALUE_VALIDATE:
                        //允许删除的ID值
                        if($v[0]){
                            $map[$v[1]] = array('in', $v[0]);
                            $options['where'] = array_merge($options['where'],$map);

                            $a_ids = $this->where($options['where'])->getField($pk, true);

                            $ins_ids = array_intersect($ids,$a_ids);
                            if(count($ins_ids) != count($ids)){
                                $this->error = $v[3];
                                return false;
                            }
                        }
                        else{
                            $this->error = $v[3];
                            return false;
                        }
                        break;
                        
                }
                
            }
        }
        
        //自动删除规则
        if(!empty($this->_delete_auto)){
            foreach($this->_delete_auto as $val){
                switch ($val[0]){
                    case 'delete':
                        if(!isset($val[1]) || !isset($val[2])){
                            $this->error = '关联删除规则错误';
                            return false;
                        }
                        
                        if(empty($val[1]) || !is_array($val[2])){
                            $this->error = '关联删除规则错误';
                            return false;
                        }
                        
                        $key = key($val[2]);
                        $fields = $this->where($options['where'])->getField($key, true);
                        if(!$fields){
                            continue;
                        }

                        $relation_model = D($val[1]);
                        if(is_array($val[3])){
                            $map = $val[3];
                        }
                        else{
                            $map = array();
                        }
                        $map[$val[2][$key]] = array('in', $fields);
                        $relation_model->where($map)->delete();
                        break;
                    default:
                        break;
                }
            }
            
        }
        return true;
    }

    protected function _checkExists($model, $field, $ids){
        $m = D($model);
        $map = array();
        
        if(empty($ids)){
            return true;
        }
        
        if(is_string($field)){
            $map[$field] = array('in', $ids);
        }
        else if(is_array($field)){
            $map = $field['where'];
            $map[$field['field']] = array('in', $ids);
        }
        $data = $m->where($map)->select();

        if($data === false){
            $this->error = 'delete_validate_error';
            return false;
        }
        return $data;
    }
    

    public function getOne($id){
        return $this->where(array('id' => $id))->find();
    }
    
    public function getOneField($id, $field, $map = array()){
        $pk = $this->getPk();
        $map[$pk] = $id;
        $ent = $this->where($map)->find();
        return $ent ? $ent[$field] : false;
    }
    
    public function getList($map = array(), $order = ''){
        if($map){
            $this->where($map);
        }
        
        if($order != ''){
            $this->order($order);
        }
        
        return $this->select();
        
//        if($order == ''){
//            return $this->where($map)->select();
//        }
//        else{
//            return $this->where($map)->order($order)->select();
//        }
    }
    
    public function getParentOptions($key, $value, $id = '', $prefix = ''){
        $map = array();
        if(!empty($id)){
            $map['id'] = array('neq', $id);
        }
        if($prefix == ''){
            $prefix = "┝ ";
        }
        $map['status'] = DBCont::NORMAL_STATUS;
        $list = $this->where($map)->select();
        $tree = list_to_tree($list);
        $select = genSelectByTree($tree);
        $options = array();
        foreach($select as $k=>$v){
            $title_prefix = str_repeat("&nbsp;", $v['level']*4);
            $title_prefix .= empty($title_prefix) ? '' : $prefix;
            $options[$v[$key]] = $title_prefix . $v[$value];
        }
        return $options;
    }
    
    public function getParentOptions1($key, $value, $map = array(), $prefix = ''){
        if($prefix == ''){
            $prefix = "┝ ";
        }
        $list = $this->where($map)->select();
        $tree = list_to_tree($list);
        $select = genSelectByTree($tree);
        $options = array();
        foreach($select as $k=>$v){
            $title_prefix = str_repeat("&nbsp;", $v['level']*4);
            $title_prefix .= empty($title_prefix) ? '' : $prefix;
            $options[$v[$key]] = $title_prefix . $v[$value];
        }
        return $options;
    }
    
    public function createAdd($data, $model = '', $key = ''){
        if($this->create($data) === false){
            return false;
        }

        $r = $this->add();
        if($r === false){
            $model != '' && $key != '' && $model->delete($key);
            return false;
        }
        else{
            return $r;
        }
    }
    
    public function createSave($data, $model = '', $old_data = ''){
        if($this->create($data) === false){
            $model != '' && $old_data != '' && $model->where(array($model->getPk() => $old_data[$model->getPk()]))->save($old_data);
            return false;
        }
        $pk         =   $this->getPk();
        if (is_string($pk) && isset($data[$pk])) {
            $where[$pk]     =   $data[$pk];
            unset($data[$pk]);
        }
        elseif (is_array($pk)) {
            // 增加复合主键支持
            foreach ($pk as $field) {
                if(isset($data[$field])) {
                    $where[$field]      =   $data[$field];
                } else {
                       // 如果缺少复合主键数据则不执行
                    $this->error        =   L('_OPERATION_WRONG_');
                    $model != '' && $old_data != '' && $model->where(array($model->getPk() => $old_data[$model->getPk()]))->save($old_data);
                    return false;
                }
                unset($data[$field]);
            }
        }
        if(!isset($where)){
            // 如果没有任何更新条件则不执行
            $this->error        =   L('_OPERATION_WRONG_');
            $model != '' && $old_data != '' && $model->where(array($model->getPk() => $old_data[$model->getPk()]))->save($old_data);
            return false;
        }
        $r = $this->where($where)->save();
        if($r === false){
            $model != '' && $old_data != '' && $model->where(array($model->getPk() => $old_data[$model->getPk()]))->save($old_data);
            return false;
        }
        else{
            return $r;
        }
    }
    
    //批量增加
    public function createAddALL($dataList,$options=array(),$replace=false){
        foreach($dataList as $v){    
            if($this->create($v) === false){
                return false;            
            }   
        }
        $r  = $this->addAll($dataList,$options=array(),$replace=false);
        return $r;
    }

    protected function _options_filter(&$options) {
        //保留不过滤机制
        if($options['cus_filter'] === false){
            return;
        }

        if(!in_array(strtolower(MODULE_NAME), C("BACKEND_MODULE"))){
            return;
        }

        if(!$this->_auth_ref_rule){
            return;
        }

        $auth = session('AUTH_RULE_ID');
        if(!$auth){
            return;
        }

        if(!isset($this->_auth_ref_rule['ref_path'])){
            return;
        }

        list($ref_model, $ref_id) = explode('.', $this->_auth_ref_rule['ref_path']);

        //检查options中有无对应key值的设置
        if(isset($options['where'][$this->_auth_ref_rule['auth_ref_key']])){
            //有对应key值
            $arr = D($ref_model)->getField($ref_id, true);
            $map[$this->_auth_ref_rule['auth_ref_key']] = $options['where'][$this->_auth_ref_rule['auth_ref_key']];
            $key_arr = $this->notOptionsFilter()->where($map)->distinct($this->_auth_ref_rule['auth_ref_key'])->getField($this->_auth_ref_rule['auth_ref_key'],true);
            $this->enableOptionsFilter();

            if(!$arr){
                $options['where']['_string'] = "1!=1";
                return;
            }

            //比较是否在范围内
            if(array_diff($key_arr, $arr)){
                //范围外
                $options['where'][$this->_activity_ref_rule['activity_ref_key']] = array('in', join(',', $arr));
            }
            else{
                return;
            }

        }
        else{

            //无对应key值，设置key值
            if($this->name == $ref_model){
                $options['where']['id'] = $auth;
            }
            else{
                $arr = D($ref_model)->getField($ref_id, true);
                if(!$arr){
                    $options['where']['_string'] = "1!=1";
                    return;
                }
                $options['where'][$this->_auth_ref_rule['auth_ref_key']] = array('in', join(',', $arr));
            }
        }
        return;

    }

    protected function _before_write(&$data) {
        if(!$this->_auth_ref_rule){
            return;
        }

        if(!in_array(strtolower(MODULE_NAME), C("BACKEND_MODULE"))){
            return;
        }

        if($this->options['cus_before_write'] === false){
            return;
        }

        $auth = session('AUTH_RULE_ID');
        if(!$auth){
            return;
        }

        if(isset($data[$this->_auth_ref_rule['auth_ref_key']])){
            list($ref_model, $ref_id) = explode('.', $this->_auth_ref_rule['ref_path']);
            $arr = D($ref_model)->getField($ref_id, true);
            if(!$arr){
                E('无权去进行意料之外的数据设置');
            }

            if(in_array($data[$this->_auth_ref_rule['auth_ref_key']], $arr)){
                return;
            }
            else{
                E('无权去进行意料之外的数据设置');
            }
        }
    }
    
    public function destory(){
        $this->db->__destruct();
    }
    
}
