<?php

namespace Admin\Controller;

use Gy_Library\DBCont;
use Gy_Library\GyListController;

class ReactBuildTestController extends GyListController
{

    public function index(){

        $status_list = collect(DBCont::getApplicatStatusList())->map(Fn($name,$key)=>['value'=>$key,'label'=>$name])->values()->all();
        $this->assign("status_list", $status_list);
        $this->display();
    }

    public function getTest($stage_id){
        if ($stage_id%2!==0){
            $this->ajaxReturn(['status' => 1, 'data' => ['stage_id'=> $stage_id]]);
        }else{
            $this->ajaxReturn(['status' => 0, 'info' => "失败用例"]);
        }
    }

    public function createTest(){
        $data = I("post.");
        if(empty($data)){
            $this->ajaxReturn(['status' => 0,  "info" => "失败"]);
        }else{
            $this->ajaxReturn(['status' => 1, 'data' => $data, "info" => "成功"]);
        }
    }

    public function updateTest(){
        $data = I("put.");

        if(empty($data)){
            $this->ajaxReturn(['status' => 0,  "info" => "失败"]);
        }else{
            $this->ajaxReturn(['status' => 1, 'data' => $data, "info" => "成功"]);
        }
    }

    public function deleteTest($id){
        if ($id%2===0){
            $this->ajaxReturn(['status' => 0,  "info" => "失败"]);
        }else{
            $this->ajaxReturn(['status' => 1, 'data' => 1, "info" => "成功"]);
        }
    }

}