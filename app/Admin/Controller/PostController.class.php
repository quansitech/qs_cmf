<?php
namespace Admin\Controller;
use Gy_Library\GyListController;
use Gy_Library\DBCont;

//自动生成代码
class PostController extends GyListController{
    
    public function index(){
        $get_data = I('get.');
        $map = array();
        if(isset($get_data['cate_id'])){
            $map['cate_id'] = $get_data['cate_id'];
        }if(isset($get_data['status'])){
            $map['status'] = $get_data['status'];
        }        
        if(isset($get_data['key']) && $get_data['word']){
            $map[$get_data['key']] = array('like', '%' . $get_data['word'] . '%');
        }        $model = D('Post');
                $count = $model->getListForCount($map);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }
        
        $data_list = $model->getListForPage($map, $page->nowPage, $page->listRows, 'sort asc');
        

        $builder = new \Common\Builder\ListBuilder();
        
        $builder = $builder->setMetaTitle('内容管理')        
        ->addSearchItem('cate_id', 'select', '所有分类', D("PostCate")->getParentOptions("id","name"))->addSearchItem('status', 'select', '所有状态', DBCont::getStatusList())        ->addSearchItem('', 'select_text', '搜索内容', array('title'=>'标题'))                
        
        ->addTopButton('addnew')
        ->addTopButton('forbid')
        ->addTopButton('resume')
        ->addTopButton('delete');
        $builder->addTopButton('save', array('title' => '保存排序'));        
        $builder->setNID(969)
        ->addTableColumn('title', '标题', '', '', false)      
        ->addTableColumn('cate_id', '所属分类', 'fun', 'D("PostCate")->getOneField(__data_id__,"name")', false)->addTableColumn('sort', '排序', '', '', true)->addTableColumn('publish_date', '发布时间', 'fun', 'date("Y-m-d",__data_id__)', false)->addTableColumn('status', '状态', 'status', '', false)        ->addTableColumn('right_button', '操作', 'btn')
        ->setTableDataList($data_list)     
        ->setTableDataPage($page->show())
        ->addRightButton('edit')         
        ->addRightButton('forbid')      
        ->addRightButton('delete')        
        ->display();
    }
    
    public function save(){
        if(IS_POST){
            $data = I('post.');
            foreach($data['id'] as $k=>$v){
                $save_data['sort'] = $data['sort'][$k];                D('Post')->where('id=' . $v)->save($save_data);
            }
            $this->success('保存成功', U('index'));
        }
    }
    
    public function add(){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');

            $model = D('Post');
            $r = $model->createAdd($data);
            if($r === false){
                $this->error($model->getError());
            }
            else{
                sysLogs('新增内容id:' . $r);

                $this->success(l('add') . l('success'), U(CONTROLLER_NAME . '/index'));
            }
        }
        else {
            $builder = new \Common\Builder\FormBuilder();
            
            $data_list = array(
            "status"=>1, 
            "publish_date" => time()
                );
            
            if($data_list){
                $builder->setFormData($data_list);
            }
            
            $builder->setMetaTitle('新增内容') 
                    ->setNID(969)
                    ->setPostUrl(U('add'))    
                    ->addFormItem('title', 'text', '标题','', '')
                    ->addFormItem('english_name', 'text', '英文标题','', '') 
                    ->addFormItem('cate_id', 'select', '所属分类','', D("PostCate")->getParentOptions("id","name"))->addFormItem('summary', 'textarea', '摘要','', '')->addFormItem('cover_id', 'picture', '封面','', '')->addFormItem('sort', 'num', '排序','', '')->addFormItem('publish_date', 'date', '发布时间','', '')->addFormItem('author', 'text', '作者','', '')->addFormItem('url', 'text', 'url','', '')->addFormItem('content', 'ueditor', '正文内容','', '')->addFormItem('video', 'text', '视频','添加优酷视频分享代码，请使用通用代码', '')->addFormItem('images', 'pictures', '图片','', '')->addFormItem('attach', 'files', '附件','', '')->addFormItem('status', 'select', '状态','', DBCont::getStatusList())
                    ->addFormItem('up','radio','置顶','',  DBCont::getBoolStatusList())
                    ->display();
        }
    }
    
    public function edit($id){
        if (IS_POST) {
            parent::autoCheckToken();
            $m_id = I('post.id');
            $data = I('post.');
            $model = D('Post');
            if(!$m_id){
                E('缺少内容ID');
            }
            
            $ent = $model->getOne($m_id);
            if(!$ent){
                E('不存在内容');
            }
            
            $ent['up'] = $data['up'];
            $ent['title'] = $data['title'];          
            $ent['cate_id'] = $data['cate_id'];
            $ent['summary'] = $data['summary'];
            $ent['cover_id'] = $data['cover_id'];
            $ent['sort'] = $data['sort'];
            $ent['english_name'] = $data['english_name'];
            $ent['publish_date'] = $data['publish_date'];
            $ent['author'] = $data['author'];$ent['url'] = $data['url'];$ent['content'] = $data['content'];$ent['video'] = $data['video'];$ent['images'] = $data['images'];$ent['attach'] = $data['attach'];$ent['status'] = $data['status'];
            if($model->createSave($ent) === false){
                $this->error($model->getError());
            }
            else{
                sysLogs('修改内容id:' . $m_id);
                $this->success('修改成功', U('index'));
            }
        } else {

            $info = D('Post')->getOne($id);


            $builder = new \Common\Builder\FormBuilder();
            $builder->setMetaTitle('编辑内容') 
                    ->setPostUrl(U('edit'))    
                    ->setNID(969)
                    ->addFormItem('id', 'hidden', 'ID')
                    ->addFormItem('title', 'text', '标题', '', '')
                    ->addFormItem('english_name', 'text', '英文标题','', '') 
                    ->addFormItem('cate_id', 'select', '所属分类', '', D("PostCate")->getParentOptions("id","name"))->addFormItem('summary', 'textarea', '摘要', '', '')->addFormItem('cover_id', 'picture', '封面', '', '')->addFormItem('sort', 'num', '排序', '', '')->addFormItem('publish_date', 'date', '发布时间', '', '')->addFormItem('author', 'text', '作者', '', '')->addFormItem('url', 'text', 'url', '', '')->addFormItem('content', 'ueditor', '正文内容', '', '')->addFormItem('video', 'text', '视频','添加优酷视频分享代码，请使用通用代码', '')->addFormItem('images', 'pictures', '图片', '', '')->addFormItem('attach', 'files', '附件', '', '')->addFormItem('status', 'select', '状态', '', DBCont::getStatusList())                    
                    ->addFormItem('up','radio','置顶','',  DBCont::getBoolStatusList())
                    ->setFormData($info)
                    ->display();
        }
    }
    
    public function forbid(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要禁用的数据');
        }
        $r = parent::_forbid($ids);
        if($r !== false){
            sysLogs('内容id: ' . $ids . ' 禁用');
            $this->success('禁用成功', U(CONTROLLER_NAME . '/index'));
        }
        else{
            $this->error($this->_getError());
        }
    }
    
    public function resume(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要启用的数据');
        }
        $r = parent::_resume($ids);
        if($r !== false){
            sysLogs('内容id: ' . $ids . ' 启用');
            $this->success('启用成功', U(CONTROLLER_NAME . '/index'));
        }
        else{
            $this->error($this->_getError());
        }
        
    }
    
    public function delete(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要删除的数据');
        }
        $r = parent::_del($ids);
        if($r === false){
            $this->error($this->_getError());
        }
        else{
            sysLogs('内容id: ' . $ids . ' 删除');
            $this->success('删除成功', U(MODULE_NAME . '/' . CONTROLLER_NAME . '/index'));
        }
    }

}