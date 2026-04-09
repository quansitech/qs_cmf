<?php

namespace Admin\Controller;
use Gy_Library\GyListController;
use Illuminate\Database\Capsule\Manager as Capsule;

class CoderController extends GyListController{
    
    public function index(){
        // CoderProxy 类不存在，此功能暂时禁用
        $this->error('代码生成器功能暂不可用，CoderProxy 组件缺失');
    }
    
    private function _parseImg($file_paths){
        $return = '';
        foreach ($file_paths as $file){
            $return .= getImgByFilePath($file);
        }
        return $return;
    }
    
    public function generate($id){
        $this->error('代码生成器功能暂不可用，CoderProxy 组件缺失');
    }
    
    public function save(){
        $this->error('代码生成器功能暂不可用，CoderProxy 组件缺失');
    }
    
    public function delete($ids){
        if(!$ids){
            $this->error('请选择要删除的项');
        }
        $ids_arr = is_array($ids) ? $ids : explode(',', $ids);
        $r = Capsule::table('coder_log')->whereIn('id', $ids_arr)->delete();
        if($r !== false){
            $this->success('删除成功', U(CONTROLLER_NAME . '/index'));
        }else{
            $this->error('删除失败');
        }
    }
    
    public function coderLog($id){
        $this->error('代码生成器功能暂不可用，CoderProxy 组件缺失');
    }

    public function edit($id){
        $this->error('代码生成器功能暂不可用，CoderProxy 组件缺失');
    }
    
//    public function test(){
//        $list = D('User')->field('id,nick_name')->select();
//    }
//    
//    public function test1(){
//        //show_bug(D("Cate")->where(array('status' => \Gy_Library\DBCont::NORMAL_STATUS))->getField('id,name'));
//        $data['id'] = 1;
//        $data['name'] = '不知道';
//        
//        $string = '<a class="label label-warning" href="/admin/coder/id/{$name}/key/{$id}/uk/{$unknow}">超级管理员无需操作</a>';
//        
//        while(preg_match('/.+\{\$(.+)\}.+/i', $string, $matches)){
//            $string = str_replace('{$' . $matches[1] . '}', $data[$matches[1]], $string);
//        }
//        
//        echo $string;
//    }
    
}

