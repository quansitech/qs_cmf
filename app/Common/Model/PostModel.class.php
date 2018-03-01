<?php
namespace Common\Model;

//自动生成代码
class PostModel extends \Gy_Library\GyListModel{

    protected $_validate = array(
     array('title', 'require', '标题必填'),array('status', 'require', '状态必填'),    );

    protected $_auto = array(
                                                                                        array('publish_date', 'strtotime', parent::MODEL_BOTH, 'function'),
                                                                                                                );
    

}