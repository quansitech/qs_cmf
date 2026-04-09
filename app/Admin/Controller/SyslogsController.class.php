<?php
namespace Admin\Controller;
use Gy_Library\GyListController;
use App\Models\Syslogs;

class SyslogsController extends GyListController{

	private function _filter($query){
		$get_data = I('get.');
		if(isset($get_data['message']) && !qsEmpty($get_data['message'])){
			$query->where('message', 'like', '%' . $get_data['message'] . '%');
		}
		if(isset($get_data['create_time'])){
			$date_range = explode('-', $get_data['create_time']);
			$start_time = strtotime(trim($date_range[0]));
			$end_time = strtotime(trim($date_range[1]) . '+1 day') - 1;
			$query->whereBetween('create_time', [$start_time, $end_time]);
		}
		if(isset($get_data['userid']) && !qsEmpty($get_data['userid'])){
			$query->where('userid', $get_data['userid']);
		}
		if(isset($get_data['opname']) && !qsEmpty($get_data['opname'])){
			$query->where('opname', $get_data['opname']);
		}
	}

    public function index(){
        $query = Syslogs::query();
	    $this->_filter($query);
        $count = (clone $query)->count();
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }

        $data_list = $query->orderBy('create_time', 'desc')
            ->offset(($page->nowPage - 1) * $page->listRows)
            ->limit($page->listRows)
            ->get()
            ->toArray();

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
        ->build();
    }
}
