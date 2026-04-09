<?php

namespace Admin\Controller;

use App\Models\Role;
use App\Models\Node;
use App\Models\Access;
use Gy_Library\DBCont;
use Gy_Library\GyListController;
use Qscmf\Lib\Inertia\Inertia;
use AntdAdmin\Component\Table;
use AntdAdmin\Component\Table\Pagination;

class RoleController extends GyListController
{
    public function index()
    {
        $get_data = I('get.');

        $query = Role::query();

        if (!empty($get_data['id'])) {
            $id = trim($get_data['id']);
            if (ctype_digit($id)) {
                $query->where('id', '=', (int)$id);
            }
        }

        if (!empty($get_data['name'])) {
            $query->where('name', 'like', '%' . trim($get_data['name']) . '%');
        }

        if (isset($get_data['status']) && $get_data['status'] !== '') {
            $query->where('status', '=', (int)$get_data['status']);
        }

        if (!empty($get_data['remark'])) {
            $query->where('remark', 'like', '%' . trim($get_data['remark']) . '%');
        }

        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if ($per_page === false) {
            $per_page = 20;
        }

        $roles = $query->orderByRaw('status desc, id desc')->paginate($per_page);

        $page = new Pagination($roles->currentPage(), $roles->perPage(), $roles->total());

        $data_list = collect($roles->items())->map(function($item){
            return $item->toArray();
        });

        $table = new Table();
        $table->setMetaTitle('用户组列表')
            ->actions(function(Table\ActionsContainer $actions){
                $actions->button('添加')->link(U('add'));
                $actions->delete();
            })
            ->columns(function (Table\ColumnsContainer $container){
                $container->text('id', 'ID');
                $container->text('name', '名称');
                $container->select('status', '状态')
                    ->setValueEnum([
                        DBCont::NORMAL_STATUS => ['text' => '启用', 'status' => 'Success'],
                        DBCont::FORBIDDEN_STATUS => ['text' => '禁用'
, 'status' => 'Error'],
                    ]);
                $container->text('remark', '备注');
                $container->action('', '操作')->actions(function(Table\ColumnType\ActionsContainer $container){
                    $container->link('编辑')->setHref(U('edit', ['id' => '__id__']));
                    $container->forbid();
                    $container->delete();
                });
            })
            ->setDataSource($data_list)
            ->setPagination($page);

        $this->setActiveNid(getNid(MODULE_NAME, CONTROLLER_NAME, 'Index'));
        $table->render();
    }

    private function _genAccessList()
    {
        $map['level'] = DBCont::LEVEL_MODULE;
        $map['status'] = DBCont::NORMAL_STATUS;
        $module_list = Node::getNodeList($map);

        $map = [];
        $map['level'] = DBCont::LEVEL_CONTROLLER;
        $map['status'] = DBCont::NORMAL_STATUS;
        $controller_list = Node::getNodeList($map);

        $map = [];
        $map['level'] = DBCont::LEVEL_ACTION;
        $map['status'] = DBCont::NORMAL_STATUS;
        $action_list = Node::getNodeList($map);

        $this->action_list = $action_list;
        $this->module_list = $module_list;
        $this->controller_list = $controller_list;
    }


    private function _createAccessList($role_id)
    {
        $auth = I('post.auth');

        $r = Access::delAccess(['role_id' => $role_id]);
        if ($r === false) {
            $this->error('删除数据时出错');
        }

        $node_arr = [];
        $access_arr = [];
        foreach ($auth as $v) {
            $access_arr = $this->getParentNode($v, $role_id, $node_arr);
        }

        if (!empty($access_arr)) {
            $r = Access::createAll($access_arr);
            if ($r === false) {
                $this->error('保存权限数据时出错');
            }
        }
    }

    private function getParentNode($node_id, $role_id, &$node_arr)
    {
        static $data_arr = [];

        $data = Node::getNode(['id' => $node_id]);

        if (!in_array($data['id'], $node_arr)) {
            $data_arr[] = ['role_id' => $role_id, 'node_id' => $data['id'], 'level' => $data['level'], 'module' => $data['name']];
            $node_arr[] = $data['id'];
        }

        if ($data['pid'] != 0) {
            $this->getParentNode($data['pid'], $role_id, $node_arr);
        }

        return $data_arr;
    }


    public function add()
    {
        parent::autoCheckToken();

        if (!empty($_POST)) {
            $data = I('post.');

            unset($data['auth']);
            $role = Role::create($data);
            if (!$role) {
                $this->error('新增失败');
            }

            $this->_createAccessList($role->id);
            sysLogs('新增用户组id: ' . $role->id);
            $this->success(l('add') . l('success'), U(CONTROLLER_NAME . '/index'));
        } else {
            $this->_genAccessList();

            Inertia::share('layoutProps.metaTitle', '新增用户组');
            $this->setActiveNid(36);
            $this->assign('nid', 36);
            $this->inertia('Role/Form', [
                'action_list' => $this->action_list,
                'module_list' => $this->module_list,
                'controller_list' => $this->controller_list,

                'submit' => [
                    'url' => U(),
                ],
            ]);
        }
    }

    public function edit($id)
    {
        parent::autoCheckToken();

        if (!empty($_POST)) {
            $data = I('post.');

            unset($data['id'], $data['auth']);
            $r = Role::find($id)->update($data);
            if ($r === false) {
                $this->error('修改失败');
            }

            $this->_createAccessList($id);
            sysLogs('修改用户组id: ' . $id);
            $this->success('修改成功', U(CONTROLLER_NAME . '/index'));
        } else {
            $vo = Role::getOne($id);

            if (empty($vo)) {
                $this->error('数据不存在');
            }

            $this->_genAccessList();

            $map['role_id'] = $vo['id'];
            $map['level'] = DBCont::LEVEL_ACTION;
            $access_list = Access::getAccessList($map);
            $auth_arr = [];
            foreach ($access_list as $v) {
                $auth_arr[] = $v['node_id'];
            }

            $vo['auth'] = $auth_arr;

            Inertia::share('layoutProps.metaTitle', '编辑用户组');
            $this->setActiveNid(36);
            $this->assign('nid', 36);
            $this->inertia('Role/Form', [
                'action_list' => $this->action_list,
                'module_list' => $this->module_list,
                'controller_list' => $this->controller_list,

                'initialValues' => $vo,

                'submit' => [
                    'url' => U('', ['id' => $id]),
                    'data' => ['id' => $id]
                ],
            ]);
        }
    }

    public function forbid()
    {
        $ids = I('ids');
        if (!$ids) {
            $this->error('请选择要禁用的数据');
        }
        $r = Role::whereIn('id', is_array($ids) ? $ids : explode(',', $ids))->update(['status' => DBCont::FORBIDDEN_STATUS]);
        if ($r !== false) {
            sysLogs('用户组id: ' . (is_array($ids) ? implode(',', $ids) : $ids) . ' 禁用');
            $this->success('禁用成功', U(CONTROLLER_NAME . '/index'));
        } else {
            $this->error('禁用失败');
        }
    }

    public function resume()
    {
        $ids = I('ids');
        if (!$ids) {
            $this->error('请选择要启用的数据');
        }
        $r = Role::whereIn('id', is_array($ids) ? $ids : explode(',', $ids))->update(['status' => DBCont::NORMAL_STATUS]);
        if ($r !== false) {
            sysLogs('用户组id: ' . (is_array($ids) ? implode(',', $ids) : $ids) . ' 启用');
            $this->success('启用成功', U(CONTROLLER_NAME . '/index'));
        } else {
            $this->error('启用失败');
        }
    }

    public function delete()
    {
        $ids = I('ids');
        if (!$ids) {
            $this->error('请选择要删除的数据');
        }
        $r = Role::destroy(is_array($ids) ? $ids : explode(',', $ids));
        if ($r === false) {
            $this->error('删除失败');
        } else {
            sysLogs('用户组id: ' . $ids . ' 删除');
            $this->success('删除成功', U(MODULE_NAME . '/' . CONTROLLER_NAME . '/index'));
        }
    }
}
