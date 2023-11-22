<?php
namespace Admin\Controller;
use Gy_Library\GyListController;
use Qscmf\Lib\DBCont;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class QueueController extends GyListController {

    public function index(){
        $get_data = I('get.');
        $map = array();
        if(isset($get_data['schedule'])){
            $map['schedule'] = $get_data['schedule'];
        }
        if(isset($get_data['status'])){
            $map['status'] = $get_data['status'];
        }
        if(isset($get_data['key']) && $get_data['word']){
            $map[$get_data['key']] = array('like', '%' . $get_data['word'] . '%');
        }        $model = D('Queue');
                $count = $model->getListForCount($map);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }

        $data_list = $model->getListForPage($map, $page->nowPage, $page->listRows, 'create_date desc');

        foreach($data_list as &$v){
            if($v['status'] != DBCont::JOB_STATUS_COMPLETE){
                $model->refreshStatusOne($v['id']);
            }

            if($v['status'] == DBCont::JOB_STATUS_FAILED || $v['status'] == DBCont::JOB_STATUS_RUNNING){
                $v['show_reset'] = 1;
            }

            if($v['schedule']){
                $v['schedule'] = '是';
            }
            else{
                $v['schedule'] = '否';
            }
        }


        $builder = new \Qscmf\Builder\ListBuilder();

        $builder = $builder->setMetaTitle('队列任务')->setCheckBox(false)
            ->addSearchItem('schedule', 'select', '是否计划任务', DBCont::getBoolStatusList())
        ->addSearchItem('status', 'select', '所有状态', DBCont::getJobStatusList())->addSearchItem('', 'select_text', '搜索内容', array('description'=>'描述'));
        $builder->addTopButton('self', array('title' => '重启全部失败任务', 'href' => U('rebuildAllFail'), 'class' => 'btn btn-primary ajax-get confirm'));
        $builder->setNIDByNode()
        ->addTableColumn('id', 'Job_id', '', '', false)
        ->addTableColumn('job', 'job','','', false)
        ->addTableColumn('description', '说明', '', '', false)->addTableColumn('status', '状态', 'fun', 'Qscmf\Lib\DBCont::getJobStatus(__data_id__)', false)
		->addTableColumn('error', '错误', '', '', false)
		->addTableColumn('schedule', '计划任务', '', '', false)
		->addTableColumn('create_date', '创建时间', 'fun', 'date("Y-m-d H:i:s", __data_id__)')
        ->addTableColumn('right_button', '操作', 'btn')
        ->setTableDataList($data_list)
        ->setTableDataPage($page->show())
         ->addRightButton('self', array('title' => '重启任务', 'href' => U('/admin/queue/rebuild', array('id' => '__data_id__')), 'class' => 'label label-success ajax-get confirm', '{key}' => 'show_reset', '{condition}' => 'eq', '{value}' => '1'))
        ->build();
    }

    public function refreshWait(){
        $map['status'] = DBCont::JOB_STATUS_WAITING;
        $data_list = D('Queue')->getList($map);
        foreach($data_list as $data){
            D('Queue')->refreshStatusOne($data['id']);
        }
        $this->success("刷新完毕", U('admin/queue/index'));
    }

    public function rebuild(){
        $job_id = I('get.id');

        $r = D('Queue')->rebuildJobOne($job_id);
        if($r === false){
            $this->error(D("Queue")->getError());
        }
        else{
            $this->success("重启成功", U('admin/queue/index'));
        }
    }

    public function rebuildAllFail(){
        $map['status'] = DBCont::JOB_STATUS_FAILED;
        $queue_list = D('Queue')->where($map)->select();
        foreach($queue_list as $q){
            D('Queue')->rebuildJobOne($q['id']);
        }
        $this->success('重启完毕', U('admin/queue/index'));
    }
}
