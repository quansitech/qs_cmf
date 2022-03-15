<?php

namespace Common\Model;
use Gy_Library\DBCont;

class AddonsModel extends \Gy_Library\GyListModel{
    
    protected $_validate = array(
        array('name', 'require', '必选填写标识'),
        array('title', 'require', '必须填写名称'),
        array('description', 'require', '必须填写描述'),
        array('status', array(DBCont::FORBIDDEN_STATUS, DBCont::NORMAL_STATUS), '{%STATUS_OUT_OF_RANGE}', parent::MUST_VALIDATE, 'in', parent::MODEL_BOTH),
        array('has_adminlist', array(0,1), '后台列表值超出有效范围', parent::MUST_VALIDATE, 'in', parent::MODEL_BOTH),
    );
    
    protected $_auto = array(
        array('create_time', "time", parent::MODEL_INSERT, 'function'),
    );
    
    public function getAllAddonsList(){
        $addon_dir = ADDON_PATH;
        $dirs = array_map('basename',glob($addon_dir.'*', GLOB_ONLYDIR));
        if($dirs === FALSE || !file_exists($addon_dir)){
            $this->error = '插件目录不可读或者不存在';
            return FALSE;
        }
       
        $addons = array();
        $where['name'] = array('in',$dirs);
        $list = $this->where($where)->field(true)->select();
        foreach($list as $addon){
            $addon['uninstall'] = 0;
            $addons[$addon['name']]	 = $addon;
        }
        foreach ($dirs as $value) {
            if(!isset($addons[$value])){
	$class = get_addon_class($value);
               
                if(!class_exists($class)){ // 实例化插件失败忽略执行
                    \Think\Log::record('插件'.$value.'的入口文件不存在！');
                     continue;
                }
                $obj = new $class;
                $addons[$value] = $obj->info;
                if($addons[$value]){
                    $addons[$value]['uninstall'] = 1;
                    $addons[$value]['status'] = -2;
                }
            }
        }
        $addons = list_sort_by($addons,'uninstall','desc');
        return $addons;
    }
}

