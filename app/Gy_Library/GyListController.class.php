<?php
namespace Gy_Library;

class GyListController extends GyController{
    
    protected $_error;
    
    protected $_factory;
    
    protected $_view;
    
    protected function _getError(){
        return $this->_error;
    }

    protected function _forbid($id){
        $model = D($this->dbname);
        $r = $model->forbid($id);
        if($r === false){
            $this->_error = $model->getError();
        }
        return $r;
    }

    protected function _resume($id){
        $model = D($this->dbname);
        $r = $model->resume($id);
        if($r === false){
            $this->_error = $model->getError();
        }
        return $r;
    }

    protected function _del($id){
        $model = D($this->dbname);
        $r = $model->del($id);
        if($r === false){
            $this->_error = $model->getError();
        }
        return $r;
    }

}

