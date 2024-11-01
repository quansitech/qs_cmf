<?php

namespace Admin\Controller;
use Gy_Library\DBCont;
use Gy_Library\GyListController;

class NodeController extends GyListController {

    public function index($status = DBCont::NORMAL_STATUS, $level = DBCont::LEVEL_ACTION){
        // 搜索
        $keyword = I('keyword', '', 'string');
        if(!empty($keyword)){
            $node_ent = D('Node')->getByName($keyword);
            $map['pid'] = $node_ent['id'];
        }

        $get_data = I('get.');
        if(isset($get_data['key']) && $get_data['word']){
            switch($get_data['key']){
              case 'controller':
                $s_map['level'] = DBCont::LEVEL_CONTROLLER;
                $s_map['name'] = $get_data['word'];
                $s_map['status'] = DBCont::NORMAL_STATUS;
                $pids = D('Node')->where($s_map)->getField('id', true);
                $map['pid'] = array('in', $pids);
                break;
              default:
                $map[$get_data['key']] = array('like', '%' . $get_data['word'] . '%');
                break;
            }

        }

        $map['level'] = $level;
        $map['status'] = $status;

        $node_model = D('Node');
        $count = $node_model->getListForCount($map);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $page = new \Gy_Library\GyPage($count);
        }
        else{
            $page = new \Gy_Library\GyPage($count, $per_page);
        }

        $data_list = $node_model->getListForPage($map, $page->nowPage, $page->listRows, 'id desc');

        foreach($data_list as &$v){
            $menu_ent = D('Menu')->getOne($v['menu_id']);
            $v['menu'] = $menu_ent['title'];

            $controller_ent = D('Node')->getOne($v['pid']);
            $v['controller'] = $controller_ent['name'];

            $module_ent = D('Node')->getOne($controller_ent['pid']);
            $v['module'] = $module_ent['name'];
        }


        // 设置Tab导航数据列表
        $status_list = DBCont::getStatusList();
        foreach ($status_list as $key => $val) {
            $tab_list[$key]['title'] = $val;
            $tab_list[$key]['href']  = U('index', array('status' => $key));
        }

        // 使用Builder快速建立列表页面。
        $builder = new \Qscmf\Builder\ListBuilder();

        $builder = $builder->setMetaTitle('节点管理')  // 设置页面标题
                                    ->addTopButton('addnew')   // 添加新增按钮
                                    ->addTopButton('self', array('title' => '权限点检查', 'href' => U('authCheck')));
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
        ->addSearchItem('', 'select_text', '搜索内容', array('name'=>'节点名称', 'controller' => '控制器', 'title' => '节点标题'))
        ->setNID(28)
        ->setTabNav($tab_list, $status)  // 设置页面Tab导航
        ->addTableColumn('id', 'ID')
        ->addTableColumn('name', '节点名称')
        ->addTableColumn('title', '节点标题')
        ->addTableColumn('sort', '排序')
        ->addTableColumn('menu', '菜单')
        ->addTableColumn('controller', '控制器')
        ->addTableColumn('module', '模块')
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

            $pid = $this->_handleController();

            $data['pid'] = $pid;
            $data['level'] = DBCont::LEVEL_ACTION;

            $node_model = D('Node');
            if(!$node_model->create($data)){
                $this->error($node_model->getError());
            }
            $r = $node_model->add();
            if($r !== false){
                sysLogs('新增节点ID:' . $r);
                $this->success(l('add') . l('success'), U(CONTROLLER_NAME . '/index'));
            }
            else{
                $this->error($node_model->getError());
            }
        }
        else {
            // 使用FormBuilder快速建立表单页面。
            $menu_model = new \Common\Model\MenuModel();

            $menu_list = $menu_model->getMenuListGroupByType();
            $this->assign('menu_list_json', json_encode($menu_list));

            $builder = new \Qscmf\Builder\FormBuilder();

            $data_list = array(
            "status"=>1,            );

            if($data_list){
                $builder->setFormData($data_list);
            }

            $builder->setMetaTitle('新增节点') //设置页面标题
                    ->setNID(28)
                    ->setPostUrl(U('add'))    //设置表单提交地址
                    ->addFormItem('name', 'text', '名称')
                    ->addFormItem('title', 'text', '标题')
                    ->addFormItem('sort', 'text', '排序')
                    ->addFormItem('icon', 'text', 'icon')
                    ->addFormItem('remark', 'text', '备注')
                    ->addFormItem('controller', 'text', '控制器')
                    ->addFormItem('module', 'text', '模块')
                    ->addFormItem('menu_type', 'select', '菜单类型', '', array(), '', 'id=cmbType')
                    ->addFormItem('menu_id', 'select', '菜单', '', array(), '', 'id=cmbMenu')
                    ->addFormItem('status', 'select', '状态', '', DBCont::getStatusList())
                    ->setExtraHtml($this->fetch('Node/add_script'))
                    ->build();
        }
    }

    public function edit($id){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');

            $node_model = new \Common\Model\NodeModel();
            $node_ent = $node_model->getOne($data['id']);

            $data = array_merge($node_ent, $data);

            $pid = $this->_handleController();

            $data['pid'] = $pid;
            if(!$node_model->create($data)){

                $this->error($node_model->getError());
            }

            $r = $node_model->edit();
            if($r !== false){
                sysLogs('修改节点ID:' . $data['post.id']);
                $this->success('修改成功', U(CONTROLLER_NAME . '/index'));
            }
            else{
                $this->error($node_model->getError());
            }
        } else {
            $node_ent = D('Node')->getOne($id);
            $controller_ent = D('Node')->getOne($node_ent['pid']);
            $module_ent = D('Node')->getOne($controller_ent['pid']);
            $node_ent['controller'] = $controller_ent['name'];
            $node_ent['module'] = $module_ent['name'];

            $menu_model = new \Common\Model\MenuModel();

            $cur_menu = $menu_model->find($node_ent['menu_id']);
            $menu_list = $menu_model->getMenuListGroupByType();
            $this->assign('menu_list_json', json_encode($menu_list));
            $this->assign('cur_menu', $cur_menu);


            // 使用FormBuilder快速建立表单页面。
            $builder = new \Qscmf\Builder\FormBuilder();
            $builder->setMetaTitle('编辑节点')  // 设置页面标题
                    ->setNID(28)
                    ->setPostUrl(U('edit'))    //设置表单提交地址
                    ->addFormItem('id', 'hidden', 'ID')
                    ->addFormItem('name', 'text', '名称')
                    ->addFormItem('title', 'text', '标题')
                    ->addFormItem('sort', 'text', '排序')
                    ->addFormItem('icon', 'text', 'icon')
                    ->addFormItem('remark', 'text', '备注')
                    ->addFormItem('controller', 'text', '控制器')
                    ->addFormItem('module', 'text', '模块')
                    ->addFormItem('menu_type', 'select', '菜单类型', '', array(), '', 'id=cmbType')
                    ->addFormItem('menu_id', 'select', '菜单', '', array(), '', 'id=cmbMenu')
                    ->addFormItem('status', 'select', '状态', '', DBCont::getStatusList())
                    ->setFormData($node_ent)
                    ->setExtraHtml($this->fetch('Node/edit_script'))
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
            sysLogs('Node id: ' . $ids . ' 禁用');
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
            sysLogs('Node id: ' . $ids . ' 启用');
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
            sysLogs('Node id: ' . $ids . ' 删除');
            $this->success('删除成功', U(MODULE_NAME . '/' . CONTROLLER_NAME . '/index'));
        }
    }

    private function _handleController(){
        //控制器必填
        if(!I('post.controller')){
            $this->error('控制器不能为空');
        }

        $controller = I('post.controller');

        if(!isEnglish($controller)){
            $this->error('必须填写英文');
        }

        $node = new \Common\Model\NodeModel();

        $controller_map = array();
        $controller_map['name'] = $controller;
        $controller_map['level'] = DBCont::LEVEL_CONTROLLER;

        $controller_node = $node->getNode($controller_map);
        if(!$controller_node){
            //控制器不存在，自动完成代码添加，并检查模块，如没有也一并添加

            //检查是否有输入模块名
            if(!I('post.module')){
                $this->error('新建控制器时,必须填写模块名!');
            }

            $module = I('post.module');

            if(!isEnglish($module)){
                $this->error('必须填写英文');
            }

            $module_map = array();
            $module_map['name'] = $module;
            $module_map['level'] = DBCont::LEVEL_MODULE;

            $module_node = $node->getNode($module_map);
            if(!$module_node){
                //模块不存在,自动创建模块代码

                $module_node['id'] = $this->_insertModule($module);
            }

            $controller_node['id'] = $this->_insertController($module, $module_node['id'], $controller);
        }
        return $controller_node['id'];
    }


    private function _insertModule($module){
        //GyBuild::buildAppDir(ucfirst($module));

        $node = new \Common\Model\NodeModel();
        $module_node = array();

        $module_node['name'] = $module;
        $module_node['title'] = $module;
        $module_node['status'] = DBCont::NORMAL_STATUS;
        $module_node['pid'] = 0;
        $module_node['level'] = DBCont::LEVEL_MODULE;

        if(!$node->create($module_node)){
            $this->error($node->getError());
        }

        $module_node['id'] = $node->add();
        if(!$module_node['id']){
            $this->error($node->getError());
        }
        else{
            return $module_node['id'];
        }
    }

    private function _insertController($module, $module_id, $controller){
        //GyBuild::buildController(ucfirst($module), ucfirst($controller));

        $node = new \Common\Model\NodeModel();

        $controller_node = array();
        $controller_node['name'] = $controller;
        $controller_node['title'] = $controller;
        $controller_node['status'] = DBCont::NORMAL_STATUS;
        $controller_node['pid'] = $module_id;
        $controller_node['level'] = DBCont::LEVEL_CONTROLLER;

        if(!$node->create($controller_node)){
            $this->error($node->getError());
        }

        $controller_node['id'] = $node->add();
        if(!$controller_node['id']){
            $this->error($node->getError());
        }
        else{
            return $controller_node['id'];
        }
    }

    public function authCheck(){

        if(IS_POST){
            $check_module = I('post.module');
            $this->assign('check_module', $check_module);
        }

        $module_list = D('Node')->getModuleList();
        $this->assign('module_list', $module_list);
        $this->display();
    }
}
