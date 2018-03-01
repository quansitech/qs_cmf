<?php

namespace Common\Model;

class FilePicModel extends \Gy_Library\GyModel{
    
    protected $_validate = array(
        array('size', 0, '文件大小要大于0', parent::MUST_VALIDATE, 'notequal', parent::MODEL_BOTH),
        array('cate', 'require', '文件类型不能为空')
    );


    protected $_auto = array(
        array('upload_date', "time", parent::MODEL_INSERT, 'function'),
    );
    
    
    public function bindUser($file_id, $user_id){
        $this->where(array('id' => $file_id))->setField('owner', $user_id);
    }
}

