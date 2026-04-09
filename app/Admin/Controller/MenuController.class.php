<?php

namespace Admin\Controller;
use App\Models\Menu;
use Gy_Library\DBCont;
use Gy_Library\GyListController;
use AntdAdmin\Component\Table;
use AntdAdmin\Component\Table\Pagination;
use AntdAdmin\Component\Tabs;
use AntdAdmin\Component\Form;
use AntdAdmin\Component\Modal\Modal;

class MenuController extends GyListController{

    public function index($status = DBCont::NORMAL_STATUS){
        $search_id = I('id', '', 'string');
        $search_title = I('title', '', 'string');
        $search_type = I('type', '', 'string');

        $query = Menu::query();

        if(!qsEmpty($search_id)){
            $query->where('id', $search_id);
        }
        if(!qsEmpty($search_title)){
            $query->where('title', 'like', '%' . $search_title . '%');
        }
        if(!qsEmpty($search_type)){
            $query->where('type', $search_type);
        }

        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        $menus = $query->where('status', $status)->orderBy('type', 'desc')->orderBy('sort', 'asc')->paginate($per_page);

        $page = new Pagination($menus->currentPage(), $menus->perPage(), $menus->total());

        $data_list = collect($menus->items())->map(function($item){
            return $item->toArray();
        });

        $table = new Table();
        $table->setMetaTitle('菜单管理')
            ->setSearch(true)
            ->actions(function(Table\ActionsContainer $actions) use ($status){
                $actions->button('添加')->modal((new Modal())->setUrl(U('add'))->setWidth(1080)->setTitle('新增菜单'));
                if($status == DBCont::NORMAL_STATUS){
                    $actions->forbid();
                }else{
                    $actions->resume();
                }
                $actions->delete();
            })
            ->columns(function (Table\ColumnsContainer $container){
                $container->text('id', 'ID');
                $container->text('title', '菜单名');
                $container->text('sort', '排序')->setSearch(false);
                $container->text('type', '分类');
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
        $tabs->setMetaTitle('菜单管理')
             ->render();
    }

    public function add(){
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');
            $data['icon'] = $data['icon'] ?? '';
            $data['url'] = $data['url'] ?? '';
            $data['module'] = $data['module'] ?? '';

            if($data['pid'] == 0){
                $data['level'] = 1;
            }
            else{
                $p_data = Menu::find($data['pid']);
                $data['level'] = $p_data->level + 1;
            }

            $menu = Menu::create($data);
            if(!$menu){
                $this->error('新增失败');
            }

            sysLogs('新增菜单:' . $menu->title);
            $this->success(l('add') . l('success'));
        }
        else {
            $menu_list = Menu::getParentOptions('id', 'title');

            $form = new Form();
            $form->setMetaTitle('新增菜单')
              ->setSubmitRequest('post', U('add'));
            $this->handleFormBuild($form, $menu_list)->render();
        }
    }

    protected function handleFormBuild(Form $form, $menu_list = []){
        $form->columns(function (Form\ColumnsContainer $columns) use ($menu_list){
            $columns->text('title', '标题');
            $columns->text('sort', '排序');
            $columns->text('icon', 'icon');
            $columns->text('type', '类型');
            $columns->text('url', 'url');
            $columns->select('pid', '父菜单')->setValueEnum($menu_list);
            $columns->text('module', '绑定模块');
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

            if(empty($data['pid'])){
                $data['pid'] = 0;
            }

            if($data['pid'] == 0){
                $data['level'] = 1;
            }
            else{
                $p_data = Menu::find($data['pid']);
                $data['level'] = $p_data->level + 1;
            }

            unset($data['id']);
            $r = Menu::find($id)->update($data);
            if($r === false){
                $this->error('修改失败');
            }

            sysLogs('修改菜单id:' . $id);
            $this->success('修改成功');
        } else {
            $menu_ent = Menu::find($id);
            if(!$menu_ent){
                E('菜单不存在');
            }

            $menu_list = Menu::getParentOptions('id', 'title', $id);

            $form = new Form();
            $form->setMetaTitle('编辑菜单')
              ->setSubmitRequest('post', U('edit', ['id' => $id]))
              ->setInitialValues($menu_ent->toArray());
            $this->handleFormBuild($form, $menu_list)->render();
        }

    }

    public function forbid(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要禁用的数据');
        }
        $r = Menu::whereIn('id', is_array($ids) ? $ids : explode(',', $ids))->update(['status' => DBCont::FORBIDDEN_STATUS]);
        if($r !== false){
            sysLogs('菜单 id: ' . (is_array($ids) ? implode(',', $ids) : $ids) . ' 禁用');
            $this->success('禁用成功');
        }
        else{
            $this->error('禁用失败');
        }
    }

    public function save(){
        if(IS_POST){
            $data = I('post.');
            foreach($data['id'] as $k => $v){
                Menu::where('id', $v)->update(['sort' => $data['sort'][$k]]);
            }
            $this->success('保存成功', U('index'));
        }
    }

    public function resume(){
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要启用的数据');
        }
        $r = Menu::whereIn('id', is_array($ids) ? $ids : explode(',', $ids))->update(['status' => DBCont::NORMAL_STATUS]);
        if($r !== false){
            sysLogs('菜单 id: ' . (is_array($ids) ? implode(',', $ids) : $ids) . ' 启用');
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
        $r = Menu::destroy(is_array($ids) ? $ids : explode(',', $ids));
        if($r === false){
            $this->error('删除失败');
        }
        else{
            sysLogs('菜单 id: ' . $ids . ' 删除');
            $this->success('删除成功');
        }
    }
}
