<?php
namespace Admin\Controller;
use Gy_Library\GyListController;
use Qscmf\Builder\ListSearchType\DateRange\DateRange;
use Qscmf\Builder\ListSearchType\Text\Text;

class SyslogsController extends GyListController{

	private function _filter(&$map){
		$get_data = I('get.');
		$map = array_merge($map, Text::parse('message', 'message', $get_data));
		$map = array_merge($map, DateRange::parse('create_time', 'create_time', $get_data));
		$map = array_merge($map, Text::parse('userid', 'userid', $get_data,'exact'));
		$map = array_merge($map, Text::parse('opname', 'opname', $get_data,'exact'));
	}

    public function index(){
	    $map = [];
	    $this->_filter($map);
        $syslog_model = D('Syslogs');
        $count = $syslog_model->getListForCount($map);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }
        
        $data_list = $syslog_model->getListForPage($map, $page->nowPage, $page->listRows, 'create_time desc');

        // 使用Builder快速建立列表页面。
        $builder = new \Qscmf\Builder\ListBuilder();
        
        $builder = $builder->setMetaTitle('系统日志')  // 设置页面标题
        ->setNIDByNode()
        ->addSearchItem('message', 'text', '消息')
        ->addSearchItem('userid', 'text', '用户ID')
        ->addSearchItem('create_time', 'date_range', '操作时间')
        ->addSearchItem('opname', 'text', '操作记录')
        ->addTableColumn('modulename', '模块名称')
        ->addTableColumn('actionname', '方法名称')
        ->addTableColumn('message', '消息')
        ->addTableColumn('userid', '用户ID')
        ->addTableColumn('userip', '用户IP')
        ->addTableColumn('create_time', '操作时间', 'fun', 'date("Y-m-d H:i:s",__data_id__)')
        ->addTableColumn('opname', '操作记录')
        ->setTableDataList($data_list)     // 数据列表
        ->setTableDataPage($page->show())  // 数据列表分页
        ->display();
    }
}







































