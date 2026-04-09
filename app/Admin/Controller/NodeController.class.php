<?php

namespace Admin\Controller;
use App\Models\Node;
use App\Models\Menu;
use Gy_Library\DBCont;
use Gy_Library\GyListController;
use AntdAdmin\Component\Table;
use AntdAdmin\Component\Table\Pagination;
use AntdAdmin\Component\Tabs;
use AntdAdmin\Component\Form;
use AntdAdmin\Component\Modal\Modal;

class NodeController extends GyListController {

    public function index($status = DBCont::NORMAL_STATUS, $level = DBCont::LEVEL_ACTION){
        $keyword = I('keyword', '', 'string');
        $get_data = I('get.');

        $query = Node::query();

        if(!empty($keyword)){
            $node_ent = Node::getNode(['name' => $keyword]);
            if($node_ent){
                $query->where('pid', $node_ent['id']);
            }
        }

        if(isset($get_data['key']) && !empty($get_data['word'])){
            switch($get_data['key']){
              case 'controller':
                $s_map = [
                    'level' => DBCont::LEVEL_CONTROLLER,
                    'name' => $get_data['word'],
                    'status' => DBCont::NORMAL_STATUS
                ];
                $pids = Node::getNodeList($s_map);
                $pid_arr = array_column($pids, 'id');
                $query->whereIn('pid', $pid_arr);
                break;
              default:
                $query->where($get_data['key'], 'like', '%' . $get_data['word'] . '%');
                break;
            }
        }

        $query->where('level', $level)->where('status', $status);

        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if($per_page === false){
            $per_page = 20;
        }
        $nodes = $query->orderBy('id', 'desc')->paginate($per_page);

        $page = new Pagination($nodes->currentPage(), $nodes->perPage(), $nodes->total());

        $data_list = collect($nodes->items())->map(function($item){
            $node = $item->toArray();

            $menu_ent = Menu::getOne($node['menu_id']);
            $node['menu'] = $menu_ent ? $menu_ent['title'] : '';

            $controller_ent = Node::getOne($node['pid']);
            $node['controller'] = $controller_ent ? $controller_ent['name'] : '';

            $module_ent = Node::getOne($controller_ent['pid']);
            $node['module'] = $module_ent ? $module_ent['name'] : '';

            return $node;
        });

        $table = new Table();
        $table->setMetaTitle('节点管理')
            ->actions(function(Table\ActionsContainer $actions) use ($status){
                $actions->button('添加')->modal((new Modal())->setUrl(U('add'))->setWidth(1080)->setTitle('新增节点'));
                if($status == DBCont::NORMAL_STATUS){
                    $actions->forbid();
                }else{
                    $actions->resume();
                }
                $actions->delete();
            })
            ->columns(function (Table\ColumnsContainer $container){
                $container->text('id', 'ID');
                $container->text('name', '节点名称');
                $container->text('title', '节点标题');
                $container->text('sort', '排序');
                $container->text('menu', '菜单');
                $container->text('controller', '控制器');
                $container->text('module', '模块');
                $container->action('', '操作')->actions(function(Table\ColumnType\ActionsContainer $container){
                    $container->link('编辑')->setHref(U('edit', ['id' => '__id__']));
                    $container->forbid();
                    $container->delete();
                });
            })
            ->setDataSource($data_list)
            ->setPagination($page);

        $tabs = new Tabs();
        $user_status_list = DBCont::getStatusList();
        foreach ($user_status_list as $key => $val) {
            if ($key == $status) {
                $tabs->addTab('tab_' . $key, $val, $table);
            } else {
                $tabs->addTab('tab_' . $key, $val, null, U('index', ['status' => $key]));
            }
        }
        $tabs->setDefaultActiveKey('tab_' . $status);

        $this->setActiveNid(getNid(MODULE_NAME, CONTROLLER_NAME, 'Index'));
        $tabs->setMetaTitle('节点管理')
             ->render();
    }

    public function add(){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');

            $pid = $this->_handleController();

            $data['pid'] = $pid;
            $data['level'] = DBCont::LEVEL_ACTION;

            $node = Node::create($data);
            if(!$node){
                $this->error('新增失败');
            }

            sysLogs('新增节点ID:' . $node->id);
            $this->success(l('add') . l('success'));
        }
        else {
            $menu_list = Menu::getMenuListGroupByType();
            $menu_options = $this->flattenMenuOptions($menu_list);

            $form = new Form();
            $form->setMetaTitle('新增节点')
              ->setSubmitRequest('post', U('add'));
            $this->handleFormBuild($form, $menu_options)->render();
        }
    }

    protected function handleFormBuild(Form $form, $menu_list = []){
        $form->columns(function (Form\ColumnsContainer $columns) use ($menu_list){
            $columns->text('name', '名称');
            $columns->text('title', '标题');
            $columns->text('sort', '排序');
            $columns->text('icon', 'icon');
            $columns->text('remark', '备注');
            $columns->text('controller', '控制器');
            $columns->text('module', '模块');
            $columns->select('menu_id', '菜单')->setValueEnum($menu_list);
            $columns->select('status', '状态')->setValueEnum(DBCont::getStatusList());
        });
        $form->actions(function (Form\ActionsContainer $actions){
            $actions->button('提交')->submit();
            $actions->button('重置')->reset();
        });
        return $form;
    }

    public function edit($id){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');

            $node_ent = Node::getOne($id);
            $data = array_merge($node_ent, $data);

            $pid = $this->_handleController();

            $data['pid'] = $pid;
            unset($data['id']);

            $r = Node::find($id)->update($data);
            if($r === false){
                $this->error('修改失败');
            }

            sysLogs('修改节点ID:' . $id);
            $this->success('修改成功', U(CONTROLLER_NAME . '/index'));
        } else {
            $node_ent = Node::getOne($id);
            if(!$node_ent){
                E('节点不存在');
            }

            $controller_ent = Node::getOne($node_ent['pid']);
            $module_ent = Node::getOne($controller_ent['pid']);
            $node_ent['controller'] = $controller_ent['name'];
            $node_ent['module'] = $module_ent['name'];

            $cur_menu = Menu::getOne($node_ent['menu_id']);
            $menu_list = Menu::getMenuListGroupByType();
            $menu_options = $this->flattenMenuOptions($menu_list);

            $form = new Form();
            $form->setMetaTitle('编辑节点')
              ->setSubmitRequest('post', U('edit', ['id' => $id]))
              ->setInitialValues($node_ent);
            $this->setActiveNid(getNid(MODULE_NAME, CONTROLLER_NAME, 'Index'));
            $this->handleFormBuild($form, $menu_options)->render();
        }
    }

    public function forbid(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要禁用的数据');
        }
        $r = Node::whereIn('id', is_array($ids) ? $ids : explode(',', $ids))->update(['status' => DBCont::FORBIDDEN_STATUS]);
        if($r !== false){
            sysLogs('Node id: ' . (is_array($ids) ? implode(',', $ids) : $ids) . ' 禁用');
            $this->success('禁用成功');
        }
        else{
            $this->error('禁用失败');
        }
    }

    public function resume(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要启用的数据');
        }
        $r = Node::whereIn('id', is_array($ids) ? $ids : explode(',', $ids))->update(['status' => DBCont::NORMAL_STATUS]);
        if($r !== false){
            sysLogs('Node id: ' . (is_array($ids) ? implode(',', $ids) : $ids) . ' 启用');
            $this->success('启用成功');
        }
        else{
            $this->error('启用失败');
        }
    }

    public function delete(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要删除的数据');
        }
        $r = Node::destroy(is_array($ids) ? $ids : explode(',', $ids));
        if($r === false){
            $this->error('删除失败');
        }
        else{
            sysLogs('Node id: ' . $ids . ' 删除');
            $this->success('删除成功');
        }
    }

    protected function flattenMenuOptions($menu_list){
        $options = [0 => '-'];
        foreach($menu_list as $type => $menus){
            foreach($menus as $menu){
                $options[$menu['id']] = $menu['title'];
            }
        }
        return $options;
    }

    private function _handleController(){
        if(!I('post.controller')){
            $this->error('控制器不能为空');
        }

        $controller = I('post.controller');

        if(!isEnglish($controller)){
            $this->error('必须填写英文');
        }

        $controller_map = [
            'name' => $controller,
            'level' => DBCont::LEVEL_CONTROLLER
        ];

        $controller_node = Node::getNode($controller_map);
        if(!$controller_node){
            if(!I('post.module')){
                $this->error('新建控制器时,必须填写模块名!');
            }

            $module = I('post.module');

            if(!isEnglish($module)){
                $this->error('必须填写英文');
            }

            $module_map = [
                'name' => $module,
                'level' => DBCont::LEVEL_MODULE
            ];

            $module_node = Node::getNode($module_map);
            if(!$module_node){
                $module_node['id'] = $this->_insertModule($module);
            }

            $controller_node['id'] = $this->_insertController($module, $module_node['id'], $controller);
        }
        return $controller_node['id'];
    }

    private function _insertModule($module){
        $module_node = [
            'name' => ucfirst($module),
            'title' => ucfirst($module),
            'status' => DBCont::NORMAL_STATUS,
            'pid' => 0,
            'level' => DBCont::LEVEL_MODULE,
            'sort' => 0
        ];

        $node = Node::create($module_node);
        if(!$node){
            $this->error('模块创建失败');
        }
        return $node->id;
    }

    private function _insertController($module, $module_id, $controller){
        $controller_node = [
            'name' => ucfirst($controller),
            'title' => ucfirst($controller),
            'status' => DBCont::NORMAL_STATUS,
            'pid' => $module_id,
            'level' => DBCont::LEVEL_CONTROLLER,
            'sort' => 0
        ];

        $node = Node::create($controller_node);
        if(!$node){
            $this->error('控制器创建失败');
        }
        return $node->id;
    }

    public function authCheck(){
        if(IS_POST){
            $check_module = I('post.module');
            $this->assign('check_module', $check_module);
        }

        $module_list = Node::getModuleList();
        $this->assign('module_list', $module_list);
        $this->display();
    }
}
