<?php

namespace Common\Coder;

abstract class Coder extends \Gy_Library\GyController{
    
    protected $_name = '';
    
    protected $_desc = '';
    
    protected $_namespace = '';
    
    protected $_view = '';

    public function getName(){
        return $this->_name;
    }
    
    public function getDesc(){
        return $this->_desc;
    }
    
    public function getImages(){
        $files = glob(APP_DIR . '/' . $this->_namespace . '/images/*.{png,jpg,bmp,jpeg,gif}', GLOB_BRACE);
        return $files;
    }
    
    public function getCoderName(){
        $separator = substr($this->_namespace, 6, 1);
        $arr = explode($separator, $this->_namespace);
        return $arr[count($arr) - 1];
    }
    
    public function getView(){
        return $this->_view;
    }
    
    abstract public function displayVew($log_id);
    
    abstract public function generate($save_flag);
    
    abstract public function logList();
}

