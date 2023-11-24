<?php
namespace Admin\Controller;
use Gy_Library\GyListController;
use Gy_Library\DBCont;
use Qscmf\Lib\Cms\CateHelperTrait;

//自动生成代码
class PostCateController extends GyListController{
    
    use CateHelperTrait;

    protected $option = [
        5 => [
            'hidden' => [
                'pid','type','introduction','img_id','cover_id','pic_id','summary','sort'
            ]
        ],
        1 => [
            'show' => ['cover_id', 'introduction']
        ],
        6=> [
            'except_self' => true,
            'show' => ['summary'],
            'hidden' => ['introduction'],
        ]

    ];

    public function index(){
        $get_data = I('get.');
        $map = array();
        if(isset($get_data['status'])){
            $map['status'] = $get_data['status'];
        }        
        if(isset($get_data['key']) && $get_data['word']){
            $map[$get_data['key']] = array('like', '%' . $get_data['word'] . '%');
        }        $model = D('PostCate');
                $data_list = $model->getList($map, 'sort asc');
        $tree = list_to_tree($data_list);
        $data_list = genSelectByTree($tree);
        foreach($data_list as $k=>$v){
            $title_prefix = str_repeat("&nbsp;", $v['level']*4);
            $title_prefix .= empty($title_prefix) ? '' : "┝ ";
            $v["name"] = $title_prefix . $v["name"];
            $data_list[$k] = $v;
        }
        

        $builder = new \Qscmf\Builder\ListBuilder();
        
        $builder = $builder->setMetaTitle('分类管理')
        
        ->addSearchItem('status', 'select', '所有状态', DBCont::getStatusList())        ->addSearchItem('', 'select_text', '搜索内容', array('name'=>'分类'))                
        
        ->addTopButton('addnew')
        ->addTopButton('forbid')
        ->addTopButton('resume')
        ->addTopButton('delete');
        $builder->addTopButton('save', array('title' => '保存排序'));        
        $builder->setNID(933)
        ->addTableColumn('name', '分类', '', '', false)->addTableColumn('sort', '排序', '', '', true)->addTableColumn('status', '状态', 'status', '', false)        ->addTableColumn('right_button', '操作', 'btn')
        ->setTableDataList($data_list)     
                ->addRightButton('edit')         
        ->addRightButton('forbid')      
        ->addRightButton('delete')        
        ->build();
    }
    
    public function save(){
        if(IS_POST){
            $data = I('post.');
            foreach($data['id'] as $k=>$v){
                $save_data['sort'] = $data['sort'][$k];                D('PostCate')->where('id=' . $v)->save($save_data);
            }
            $this->success('保存成功', U('index'));
        }
    }
    
    public function add(){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');

            $model = D('PostCate');
            $r = $model->createAdd($data);
            if($r === false){
                $this->error($model->getError());
            }
            else{
                sysLogs('新增分类id:' . $r);

                $this->success(l('add') . l('success'), U(CONTROLLER_NAME . '/index'));
            }
        }
        else {
            $builder = new \Qscmf\Builder\FormBuilder();
            
            $data_list = array(
            "status"=>1,            );
            
            if($data_list){
                $builder->setFormData($data_list);
            }
            
            $builder->setMetaTitle('新增分类') 
                    ->setNID(933)
                    ->setPostUrl(U('add'))    
                    ->addFormItem('name', 'text', '分类','', '')->addFormItem('pid', 'select', '上级分类','', D("PostCate")->getParentOptions("id","name",$id))->addFormItem('summary', 'textarea', '摘要','', '')->addFormItem('cover_id', 'picture', '分类封面','', '')->addFormItem('sort', 'num', '排序','', '')->addFormItem('url', 'text', 'url','', '')->addFormItem('content', 'ueditor', '分类详情','', '')->addFormItem('status', 'select', '状态','', DBCont::getStatusList())                    ->build();
        }
    }
    
    public function edit($id){
        if (IS_POST) {
            parent::autoCheckToken();
            $m_id = I('post.id');
            $data = I('post.');
            $model = D('PostCate');
            if(!$m_id){
                E('缺少分类ID');
            }
            
            $ent = $model->getOne($m_id);
            if(!$ent){
                E('不存在分类');
            }
            
            $ent['name'] = $data['name'];$ent['pid'] = $data['pid'];$ent['summary'] = $data['summary'];$ent['cover_id'] = $data['cover_id'];$ent['sort'] = $data['sort'];$ent['url'] = $data['url'];$ent['content'] = $data['content'];$ent['status'] = $data['status'];
            if($model->createSave($ent) === false){
                $this->error($model->getError());
            }
            else{
                sysLogs('修改分类id:' . $m_id);
                $this->success('修改成功', U('index'));
            }
        } else {

            $info = D('PostCate')->getOne($id);


            $builder = new \Qscmf\Builder\FormBuilder();
            $builder->setMetaTitle('编辑分类') 
                    ->setPostUrl(U('edit'))    
                    ->setNID(933)
                    ->addFormItem('id', 'hidden', 'ID')
                    ->addFormItem('name', 'text', '分类', '', '')->addFormItem('pid', 'select', '上级分类', '', D("PostCate")->getParentOptions("id","name",$id))->addFormItem('summary', 'textarea', '摘要', '', '')->addFormItem('cover_id', 'picture', '分类封面', '', '')->addFormItem('sort', 'num', '排序', '', '')->addFormItem('url', 'text', 'url', '', '')->addFormItem('content', 'ueditor', '分类详情', '', '')->addFormItem('status', 'select', '状态', '', DBCont::getStatusList())                    ->setFormData($info)
                    ->setFormItemFilter($this->formItemFilter($this->option))
                    ->build();
        }
    }
    
    public function forbid(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要禁用的数据');
        }
        $r = parent::_forbid($ids);
        if($r !== false){
            sysLogs('分类id: ' . $ids . ' 禁用');
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
            sysLogs('分类id: ' . $ids . ' 启用');
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
            sysLogs('分类id: ' . $ids . ' 删除');
            $this->success('删除成功', U(MODULE_NAME . '/' . CONTROLLER_NAME . '/index'));
        }
    }

}