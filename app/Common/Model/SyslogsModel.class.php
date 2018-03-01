<?php
namespace Common\Model;

class SyslogsModel extends \Gy_Library\GyListModel{
	
	protected $model_name = '系统日志';

	
	public function sysLogsSearch($search_value){
		$map['actionname'] = array('LIKE','%'.$search_value.'%');
		return $this->where($map)->select();
	}
	

        
}