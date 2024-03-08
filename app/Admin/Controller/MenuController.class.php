<?php

namespace Admin\Controller;
use Gy_Library\GyListController;
use Gy_Library\DBCont;

class MenuController extends GyListController{

    public function index($status = DBCont::NORMAL_STATUS){
        $map['status'] = $status;

        $menu_model = D('Menu');
        $count = $menu_model->getListForCount($map);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }

        $data_list = $menu_model->getListForPage($map, $page->nowPage, $page->listRows, 'type desc, sort asc');

        // 设置Tab导航数据列表
        $status_list = DBCont::getStatusList();
        foreach ($status_list as $key => $val) {
            $tab_list[$key]['title'] = $val;
            $tab_list[$key]['href']  = U('index', array('status' => $key));
        }

        // 使用Builder快速建立列表页面。
        $builder = new \Qscmf\Builder\ListBuilder();

        $builder = $builder->setMetaTitle('菜单管理')  // 设置页面标题
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
        ->addTopButton('save')
        ->setNID(24)
        ->setTabNav($tab_list, $status)  // 设置页面Tab导航
        ->addTableColumn('id', 'ID')
        ->addTableColumn('title', '菜单名')
        ->addTableColumn('sort', '排序', '', '', true)
        ->addTableColumn('type', '分类')
        ->addTableColumn('right_button', '操作', 'btn')
        ->setTableDataList($data_list)     // 数据列表
        ->setTableDataPage($page->show())  // 数据列表分页
        ->addRightButton('edit')           // 添加编辑按钮
        ->addRightButton('forbid')         // 添加禁用/启用按钮
        ->addRightButton('delete')         // 添加删除按钮
        ->build();
    }

    public function add(){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');

            if($data['pid'] == 0){
                $data['level'] = 1;
            }
            else{
                $p_data = D('Menu')->find($data['pid']);
                $data['level'] = $p_data['level'] + 1;
            }

            $menu_model = D('Menu');
            $r = $menu_model->createAdd($data);
            if($r === false){
                $this->error($menu_model->getError());
            }

            $menu_ent = D('menu')->find($r);
            sysLogs('新增菜单:' . $menu_ent['title']);
            $this->success(l('add') . l('success'), U(CONTROLLER_NAME . '/index'));
        }
        else {
            // 使用FormBuilder快速建立表单页面。
            $menu_model = new \Common\Model\MenuModel();
            // $map['status'] = DBCont::NORMAL_STATUS;
            // $menu_list = $menu_model->where($map)->getField('id,title');
            $menu_list = $menu_model->getParentOptions("id","title");

            $builder = new \Qscmf\Builder\FormBuilder();
            $builder->setMetaTitle('新增菜单') //设置页面标题
                    ->setNID(24)
                    ->setPostUrl(U('add'))    //设置表单提交地址
                    ->addFormItem('title', 'text', '标题')
                    ->addFormItem('sort', 'text', '排序')
                    ->addFormItem('icon', 'text', 'icon')
                    ->addFormItem('type', 'text', '类型')
                    ->addFormItem('url', 'text', 'url')
                    ->addFormItem('pid', 'select', '父菜单', '', $menu_list)
                    ->addFormItem('module', 'text', '绑定模块')
                    ->addFormItem('status', 'select', '状态', '', DBCont::getStatusList())
                    ->build();
        }
    }

    public function edit($id){
       if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');

            if(empty($data['pid'])){
                $data['pid'] = 0;
            }

            if($data['pid'] == 0){
                $data['level'] = 1;
            }
            else{
                $p_data = D('Menu')->find($data['pid']);
                $data['level'] = $p_data['level'] + 1;
            }

            $menu_model = D('Menu');
            $r = $menu_model->createSave($data);
            if($r === false){
                $this->error($menu_model->getError());
            }

            sysLogs('修改菜单id:' . $data['id']);
            $this->success('修改成功', U(CONTROLLER_NAME . '/index'));
        } else {
            $menu_ent = D('Menu')->where(array('status' => DBCont::NORMAL_STATUS, 'id' => $id))->find();
            if(!$menu_ent){
                E('菜单不存在');
            }

            // $map['status'] = DBCont::NORMAL_STATUS;
            // $map['id'] = array('neq', $id);
            // $menu_list = D('Menu')->where($map)->getField('id,title');

            $menu_list = D('Menu')->getParentOptions("id","title", $id);

            // 使用FormBuilder快速建立表单页面。
            $builder = new \Qscmf\Builder\FormBuilder();
            $builder->setMetaTitle('编辑菜单')  // 设置页面标题
                    ->setNID(24)
                    ->setPostUrl(U('edit'))    //设置表单提交地址
                    ->addFormItem('id', 'hidden', 'ID')
                    ->addFormItem('title', 'text', '标题')
                    ->addFormItem('sort', 'text', '排序')
                    ->addFormItem('icon', 'text', 'icon')
                    ->addFormItem('type', 'text', '类型')
                    ->addFormItem('url', 'text', 'url')
                    ->addFormItem('pid', 'select', '父菜单', '', $menu_list)
                    ->addFormItem('module', 'text', '绑定模块')
                    ->addFormItem('status', 'select', '状态', '', DBCont::getStatusList())
                    ->setFormData($menu_ent)
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
            sysLogs('菜单 id: ' . $ids . ' 禁用');
            $this->success('禁用成功', U(CONTROLLER_NAME . '/index'));
        }
        else{
            $this->error($this->_getError());
        }
    }

    public function save(){
        if(IS_POST){
            $data = I('post.');
            foreach($data['id'] as $k=>$v){
                $save_data['sort'] = $data['sort'][$k];                D('Menu')->where('id=' . $v)->save($save_data);
            }
            $this->success('保存成功', U('index'));
        }
    }

    public function resume(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要启用的数据');
        }
        $r = parent::_resume($ids);
        if($r !== false){
            sysLogs('菜单 id: ' . $ids . ' 启用');
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
            sysLogs('菜单 id: ' . $ids . ' 删除');
            $this->success('删除成功', U(MODULE_NAME . '/' . CONTROLLER_NAME . '/index'));
        }
    }
}
