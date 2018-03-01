<?php
namespace Common\Coder;

class CoderProxy{
    
    protected $_coder_object_list = array();
    
    public function __construct() {
        $dirs = array_map('basename', glob(CODER_DIR .'/*', GLOB_ONLYDIR));
        foreach($dirs as $dir){
            $class_name = "\Common\Coder\\{$dir}\\{$dir}Coder";
            $this->_coder_object_list[$dir] = new $class_name();
        }
    }
    
    public function getObjectList(){
        return $this->_coder_object_list;
    }
    
    public function getObject($id){
        $class_name = "\Common\Coder\\{$id}\\{$id}Coder";
        return new $class_name();
    }
}

