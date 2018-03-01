<?php
namespace Common\Model;

//自动生成代码
class PostCateModel extends \Gy_Library\GyListModel{

    protected $_validate = array(
     array('name', 'require', '分类必填'),array('status', 'require', '状态必填'),    );

    protected $_auto = array(
                                                                                                                                            );
}