<?php
namespace Admin\Controller;
use Gy_Library\DBCont;

class HooksController extends \Gy_Library\GyListController{
    
    public function index($status = DBCont::NORMAL_STATUS){
        $map['status'] = $status;

        $hooks_model = D('Hooks');
        $count = $hooks_model->getListForCount($map);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }
        
        $data_list = $hooks_model->getListForPage($map, $page->nowPage, $page->listRows, 'id desc');
       
        foreach($data_list as $key => $hook){
            $addons = getHookAddons($hook['name']);
            $hook['addons'] = implode(',', $addons);
            $data_list[$key] = $hook;
        }

        // 设置Tab导航数据列表
        $status_list = DBCont::getStatusList();
        foreach ($status_list as $key => $val) {
            $tab_list[$key]['title'] = $val;
            $tab_list[$key]['href']  = U('index', array('status' => $key));
        }

        // 使用Builder快速建立列表页面。
        $builder = new \Qscmf\Builder\ListBuilder();
        
        $builder = $builder->setMetaTitle('钩子列表')  // 设置页面标题
                                    ->addTopButton('addnew');   // 添加新增按钮

        switch($status){
            case DBCont::NORMAL_STATUS;
                $builder = $builder->addTopButton('forbid');   // 添加禁用按钮
                break;
            case DBCont::FORBIDDEN_STATUS;
                $builder = $builder->addTopButton('resume');   // 添加启用按钮
                break;
            default:
                break;
        }

        $builder->addTopButton('delete')   // 添加删除按钮
        ->setNID(122)
        ->setTabNav($tab_list, $status)  // 设置页面Tab导航
        ->addTableColumn('name', '名称')
        ->addTableColumn('desc', '描述')
        ->addTableColumn('addons', '挂载插件')
        ->addTableColumn('right_button', '操作', 'btn')
        ->setTableDataList($data_list)     // 数据列表
        ->setTableDataPage($page->show())  // 数据列表分页
        ->addRightButton('edit')           // 添加编辑按钮
        ->addRightButton('forbid')         // 添加禁用/启用按钮
        ->addRightButton('delete')         // 添加删除按钮
        ->display();
    }
    
    public function add(){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');
        
            $hooks_model = D('Hooks');
            $r = $hooks_model->createAdd($data);
            if($r === false){
                $this->error($hooks_model->getError());
            }
            else{
                $hooks_ent = D('Hooks')->find($r);
                sysLogs('新增钩子:' . $hooks_ent['name']);
                $this->success(l('add') . l('success'), U(CONTROLLER_NAME . '/index'));
            }
        }
        else {
            // 使用FormBuilder快速建立表单页面。
            $builder = new \Qscmf\Builder\FormBuilder();
            $builder->setMetaTitle('新增钩子') //设置页面标题
                    ->setNID(122)
                    ->setPostUrl(U('add'))    //设置表单提交地址
                    ->addFormItem('name', 'text', '钩子名称')
                    ->addFormItem('desc', 'text', '钩子描述')
                    ->display();
        }
    }
    
    public function edit($id){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');
            
            $hooks_model = D('Hooks');
            $r = $hooks_model->createSave($data);
            if($r === false){
                $this->error($hooks_model->getError());
            }

            sysLogs('修改钩子id:' . $data['id']);
            $this->success('修改成功', U(CONTROLLER_NAME . '/index'));
        } else {
            // 获取账号信息
            $info = D('Hooks')->getOne($id);
            
            // 使用FormBuilder快速建立表单页面。
            $builder = new \Qscmf\Builder\FormBuilder();
            $builder->setMetaTitle('编辑钩子') //设置页面标题
                    ->setNID(122)
                    ->setPostUrl(U('edit'))    //设置表单提交地址
                    ->addFormItem('id', 'hidden', 'ID')
                    ->addFormItem('name', 'text', '钩子名称')
                    ->addFormItem('desc', 'text', '钩子描述')
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
            $map['id'] = array('in', explode(',', $ids));
            $names = M('hooks')->where($map)->getField('name', true);
            syslogs('禁用钩子:' . implode(',', $names));
            $this->success('禁用成功');
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
            $map['id'] = array('in', explode(',', $ids));
            $names = M('hooks')->where($map)->getField('name', true);
            syslogs('启用钩子:' . implode(',', $names));
            $this->success('启用成功');
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
            $map['id'] = array('in', explode(',', $ids));
            $names = M('hooks')->where($map)->getField('name', true);
            syslogs('删除钩子:' . implode(',', $names));
            $this->success('删除成功');
        }
    }
    
}

