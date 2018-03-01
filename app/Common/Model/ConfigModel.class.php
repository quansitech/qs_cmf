<?php
namespace Common\Model;
use Gy_Library\DBCont;

class ConfigModel extends \Gy_Library\GyListModel{
    
    protected $model_name = '配置';
    
    protected $_validate = array(
        array('name','require', '配置名称必填'),
        array('type','require', '配置类型必填'),
        //array('type','/^[0-9]+$/','{%MUST_BE_INTEGER}',parent::MUST_VALIDATE,'regex'),
        array('title','require', '配置标题必填'),
        array('group','require', '配置分组必填'),
        array('group','/^[0-9]+$/','{%MUST_BE_INTEGER}',parent::MUST_VALIDATE,'regex'),
        array('status',array(DBCont::FORBIDDEN_STATUS, DBCont::NORMAL_STATUS),'{%STATUS_OUT_OF_RANGE}',parent::MUST_VALIDATE, in, parent::MODEL_BOTH),
    );
    
    protected $_delete_validate = array(
        array(array('2'), 'group', parent::NOT_ALLOW_VALUE_VALIDATE, '不能删除系统组的配置项'),
    );
    
    protected $_forbid_validate = array(
        array(array('2'), 'group', parent::NOT_ALLOW_VALUE_VALIDATE, '不能禁用系统组的配置项'),
    );
    
    protected $_auto = array(
        array('status', DBCont::NORMAL_STATUS, parent::MODEL_INSERT),
        array('create_time', "time", parent::MODEL_INSERT, 'function'),
        array('update_time', "time", parent::MODEL_BOTH, 'function'),
    );
    
    
    public function lists(){
        $map    = array('status' => DBCont::NORMAL_STATUS);
        $data   = $this->where($map)->field('type,name,value')->select();
        
        $config = array();
        if($data && is_array($data)){
            foreach ($data as $value) {
                $config[$value['name']] = $this->_parse($value['type'], $value['value']);
            }
        }
        return $config;
    }
    
    public function getConfigList($map){
        return $this->where($map)->order('sort')->select();
    }
    
    /**
     * 根据配置类型解析配置
     * @param  integer $type  配置类型
     * @param  string  $value 配置值
     */
    private function _parse($type, $value){
        switch ($type) {
            case 'array':
                $re_value = parse_config_attr($value);
                break;
            default:
                $re_value = $value;
                break;
        }
        return $re_value;
    }
    
    
    public function updateConfig($name, $value){
        $map = array('name' => $name);
        $r = $this->where($map)->setField('value', $value);
        if($r !== false){
            sysLogs('修改配置|'. $name . '=' . $value);
        }
        return $r;
    }
    
}