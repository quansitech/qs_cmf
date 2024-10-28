<?php

namespace Admin\Controller;

use Gy_Library\DBCont;
use Gy_Library\GyListController;
use Qscmf\Lib\Inertia\Inertia;

class RoleController extends GyListController
{
    public function index()
    {
        $keyword = I('keyword', '', 'string');
        $condition = array('like', '%' . $keyword . '%');
        $map['name'] = $condition;

        $role_model = D('Role');
        $count = $role_model->getListForCount($map);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if ($per_page === false) {
            $page = new \Gy_Library\GyPage($count);
        } else {
            $page = new \Gy_Library\GyPage($count, $per_page);
        }

        $data_list = $role_model->getListForPage($map, $page->nowPage, $page->listRows, 'status desc, id desc');

        // 使用Builder快速建立列表页面。
        $builder = new \Qscmf\Builder\ListBuilder();

        $builder = $builder->setMetaTitle('用户组列表')  // 设置页面标题
        ->addTopButton('addnew')  // 添加新增按钮

        ->addTopButton('delete')   // 添加删除按钮
//        ->setSearch(
//            '名称',
//            U('index')
//        )
        ->setNID(36)
            ->addTableColumn('id', 'ID')
            ->addTableColumn('name', '名称')
            ->addTableColumn('status', '状态', "status")
            ->addTableColumn('remark', '备注')
            ->addTableColumn('right_button', '操作', 'btn')
            ->setTableDataList($data_list)     // 数据列表
            ->setTableDataPage($page->show())  // 数据列表分页
            ->addRightButton('edit')           // 添加编辑按钮
            ->addRightButton('forbid')         // 添加禁用/启用按钮
            ->addRightButton('delete')         // 添加删除按钮
            ->build();
    }

    private function _genAccessList()
    {
        $node = new \Common\Model\NodeModel();
        $map['level'] = DBCont::LEVEL_MODULE;
        $map['status'] = DBCont::NORMAL_STATUS;
        $module_list = $node->getNodeList($map);

        $map = array();
        $map['level'] = DBCont::LEVEL_CONTROLLER;
        $map['status'] = DBCont::NORMAL_STATUS;
        $controller_list = $node->getNodeList($map);

        $map = array();
        $map['level'] = DBCont::LEVEL_ACTION;
        $map['status'] = DBCont::NORMAL_STATUS;
        $action_list = $node->getNodeList($map);

        $this->assign('action_list', $action_list);
        $this->assign('module_list', $module_list);
        $this->assign('controller_list', $controller_list);
    }


    private function _createAccessList($role_id)
    {
        $auth = I('post.auth');

        $access = new \Common\Model\AccessModel();
        $map['role_id'] = $role_id;
        $r = $access->delAccess($map);
        if ($r === false) {
            $this->error('删除数据时出错');
        }

        $node = new \Common\Model\NodeModel();

        $node_arr = array();
        $access_arr = array();
        foreach ($auth as $v) {
            $access_arr = $this->getParentNode($v, $role_id, $node_arr);
        }

        if (!empty($access_arr)) {

            $r = $access->addAll($access_arr);
            if ($r === false) {
                $this->error($access->getError());
            }
        }
    }

    //组装要插入gy_access的数组
    private function getParentNode($node_id, $role_id, &$node_arr)
    {
        static $data_arr = array();

        $node = new \Common\Model\NodeModel();
        $map['id'] = $node_id;
        $data = $node->getNode($map);

        if (!in_array($data['id'], $node_arr)) {
            $data_arr[] = array('role_id' => $role_id, 'node_id' => $data['id'], 'level' => $data['level'], 'module' => $data['name']);
            $node_arr[] = $data['id'];
        }

        if ($data['pid'] != 0) {
            $this->getParentNode($data['pid'], $role_id, $node_arr);
        }

        return $data_arr;
    }


    public function add()
    {
        //重复提交处理
        parent::autoCheckToken();

        if (!empty($_POST)) {
            $data = I('post.');

            $model = D($this->dbname);
            if (!$model->create($data)) {
                $this->error($model->getError());
            }
            $r = $model->add();
            if ($r !== false) {
                $this->_createAccessList($r);
                sysLogs('新增用户组id: ' . $r);
                $this->success(l('add') . l('success'), U(CONTROLLER_NAME . '/index'));
            } else {
                $this->error($model->getError());
            }
        } else {
            $this->_genAccessList();

            if (C('ANTD_ADMIN_BUILDER_ENABLE')) {
                Inertia::getInstance()->share('layoutProps.metaTitle', '新增用户组');
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
                return;
            }
            $this->display();
        }
    }

    public function edit($id)
    {
        parent::autoCheckToken();

        if (!empty($_POST)) {
            $data = I('post.');

            $model = D($this->dbname);
            if (!$model->create($data)) {

                $this->error($model->getError());
            }

            $r = $model->edit();
            if ($r !== false) {

                $this->_createAccessList($id);
                sysLogs('修改用户组id: ' . $id);

                $this->success('修改成功', U(CONTROLLER_NAME . '/index'));
            } else {

                $this->error($model->getError());
            }
        } else {
            $model = D($this->dbname);

            $vo = $model->find($id);

            if (empty($vo)) {
                $this->error('数据不存在');
            }

            $this->_genAccessList();

            $access = new \Common\Model\AccessModel();
            $map['role_id'] = $vo['id'];
            $map['level'] = DBCont::LEVEL_ACTION;
            $access_list = $access->getAccessList($map);
            $auth_arr = [];
            foreach ($access_list as $v) {
                array_push($auth_arr, $v['node_id']);
            }

            $vo['auth'] = $auth_arr;

            if (C('ANTD_ADMIN_BUILDER_ENABLE')) {
                Inertia::getInstance()->share('layoutProps.metaTitle', '编辑用户组');
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
                return;
            }

            $this->assign('vo', $vo);
            $this->display();
        }
    }

    public function forbid()
    {
        $ids = I('ids');
        if (!$ids) {
            $this->error('请选择要禁用的数据');
        }
        $r = parent::_forbid($ids);
        if ($r !== false) {
            sysLogs('用户组id: ' . $ids . ' 禁用');
            $this->success('禁用成功', U(CONTROLLER_NAME . '/index'));
        } else {
            $this->error($this->_getError());
        }
    }

    public function resume()
    {
        $ids = I('ids');
        if (!$ids) {
            $this->error('请选择要启用的数据');
        }
        $r = parent::_resume($ids);
        if ($r !== false) {
            sysLogs('用户组id: ' . $ids . ' 启用');
            $this->success('启用成功', U(CONTROLLER_NAME . '/index'));
        } else {
            $this->error($this->_getError());
        }

    }

    public function delete()
    {
        $ids = I('ids');
        if (!$ids) {
            $this->error('请选择要删除的数据');
        }
        $r = parent::_del($ids);
        if ($r === false) {
            $this->error($this->_getError());
        } else {
            sysLogs('用户组id: ' . $ids . ' 删除');
            $this->success('删除成功', U(MODULE_NAME . '/' . CONTROLLER_NAME . '/index'));
        }
    }
}