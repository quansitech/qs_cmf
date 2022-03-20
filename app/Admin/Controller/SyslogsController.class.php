<?php
namespace Admin\Controller;
use Gy_Library\GyListController;

class SyslogsController extends GyListController{

    public function index(){
        $syslog_model = D('Syslogs');
        $count = $syslog_model->getListForCount();
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }
        
        $data_list = $syslog_model->getListForPage('', $page->nowPage, $page->listRows, 'create_time desc');

        // 使用Builder快速建立列表页面。
        $builder = new \Qscmf\Builder\ListBuilder();
        
        $builder = $builder->setMetaTitle('系统日志')  // 设置页面标题
        ->setNIDByNode()
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







































