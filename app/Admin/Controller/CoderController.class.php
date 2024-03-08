<?php

namespace Admin\Controller;
use Gy_Library\GyListController;

class CoderController extends GyListController{
    
    public function index(){

        $proxy = new \Common\Coder\CoderProxy();
        $object_list = $proxy->getObjectList();
        $data_list = array();
        foreach($object_list as $object){
            $data['id'] = $object->getCoderName();
            $data['name'] = $object->getName();
            $data['desc'] = $object->getDesc();
            $data['images'] = $this->_parseImg($object->getImages());
            $data_list[] = $data;
        }
        
        $builder = new \Qscmf\Builder\ListBuilder();
        
        $builder->setMetaTitle('代码生成器列表')
                    ->setNID(301)
                    ->setCheckBox(false)
                    ->addTableColumn('name', '名称')
                    ->addTableColumn('desc', '描述')
                    ->addTableColumn('images', '效果图')
                    ->addTableColumn('right_button', '操作', 'btn')
                    ->setTableDataList($data_list)
                    ->addRightButton('self', array('title' => '生成器', 'href' => U('generate', array('id' => '__data_id__')) , 'data-id' => '__data_id__', 'class' => 'label label-primary'))
                    ->build();
    }
    
    private function _parseImg($file_paths){
        $return = '';
        foreach ($file_paths as $file){
            $return .= getImgByFilePath($file);
        }
        return $return;
    }
    
    public function generate($id){
        $proxy = new \Common\Coder\CoderProxy();
        
        if(IS_POST){
            $data = I('post.');
            $coder_object = $proxy->getObject($data['id']);
            $coder_object->generate();
        }
        else{
            $coder_object = $proxy->getObject($id);
            $coder_object->displayVew();
        }
    }
    
    public function save(){
        $proxy = new \Common\Coder\CoderProxy();
        if(IS_POST){
            $data = I('post.');
            
            $coder_object = $proxy->getObject($data['id']);
            $coder_object->generate(1);
        }
    }
    
    public function delete($ids){
        if(!$ids){
            $this->error('请选择要删除的项');
        }
        $this->dbname = 'CoderLog';
        $r = parent::_del($ids);
        if($r !== false){
            $this->success('删除成功', U(CONTROLLER_NAME . '/index'));
        }else{
            $this->error($this->_getError());
        }
    }
    
    public function coderLog($id){
        $proxy = new \Common\Coder\CoderProxy();
        $coder_object = $proxy->getObject($id);
        $coder_object->logList();
    }
    
    public function edit($id){
        $ent = D('CoderLog')->getOne($id);
        $proxy = new \Common\Coder\CoderProxy();
        $coder_object = $proxy->getObject($ent['coder_name']);
        $coder_object->displayVew($id);
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

