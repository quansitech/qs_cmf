<?php

namespace Admin\Controller;

use Gy_Library\DBCont;
use Gy_Library\GyListController;
use Qscmf\Builder\FormBuilder;

class UserController extends GyListController
{

    public function test()
    {
        D('TeamMember')->isMember(136, 2);
    }

    public function index($status = DBCont::NORMAL_STATUS)
    {
        // 搜索
        $keyword = I('keyword', '', 'string');
        $condition = array('like', '%' . $keyword . '%');
        $map['id|nick_name|email|telephone'] = array(
            $condition,
            $condition,
            $condition,
            $condition,
            '_multi' => true
        );

        $map['status'] = $status;
        $map['user_type'] = 'system';

        $user_model = D('User');
        $count = $user_model->getListForCount($map);
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);
        if ($per_page === false) {
            $page = new \Gy_Library\GyPage($count);
        } else {
            $page = new \Gy_Library\GyPage($count, $per_page);
        }

        $data_list = $user_model->getListForPage($map, $page->nowPage, $page->listRows, 'register_date desc');
        foreach ($data_list as &$data) {
            $role_ids = D('RoleUser')->where('user_id=' . $data['id'])->getField('role_id', true);
            if ($role_ids) {
                $role_map['id'] = array('in', $role_ids);
                $role_map['status'] = DBCont::NORMAL_STATUS;
                $data['role'] = D('Role')->where($role_map)->getField('name', true);
                $data['role'] = implode(',', (array)$data['role']);
            }

            $data['change_password_modal'] = $this->buildPasswordModal($data['id']);
//            dd($data['change_password_modal']);
        }


        // 设置Tab导航数据列表
        $user_status_list = DBCont::getUserStatusList();
        foreach ($user_status_list as $key => $val) {
            $tab_list[$key]['title'] = $val;
            $tab_list[$key]['href'] = U('index', array('status' => $key));
        }

        // 使用Builder快速建立列表页面。
        $builder = new \Qscmf\Builder\ListBuilder();

        $builder = $builder->setMetaTitle('账号列表')  // 设置页面标题
        ->addTopButton('addnew')   // 添加新增按钮
        ->addSearchItem('keyword', 'text', 'id/昵称/email/手机号');

        switch ($status) {
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
        ->setNID(5)
            ->setTabNav($tab_list, $status)  // 设置页面Tab导航
            ->addTableColumn('id', 'ID')
            ->addTableColumn('nick_name', '用户名')
            ->addTableColumn('email', '邮箱')
            ->addTableColumn('telephone', '手机')
            ->addTableColumn('role', '用户组')
            ->addTableColumn('right_button', '操作', 'btn')
            ->setTableDataList($data_list)     // 数据列表
            ->setTableDataPage($page->show())  // 数据列表分页
            ->addRightButton('edit')           // 添加编辑按钮
            ->addRightButton('modal', ['title' => '修改密码'], '', '', 'change_password_modal')
//        ->addRightButton('self', array('title' => '修改密码','href'=>'#', 'data-id' => '__data_id__', 'class' => 'label label-default repwd-btn', 'data-toggle' => 'modal', 'data-target' => '#changepassword'))
            ->addRightButton('self', array('title' => '激活', 'href' => U('active', array('ids' => '__data_id__')), 'class' => 'label label-primary', '{key}' => 'status', '{condition}' => 'eq', '{value}' => '2'))
            ->addRightButton('forbid')         // 添加禁用/启用按钮
            ->addRightButton('delete')         // 添加删除按钮
            ->setExtraHtml($this->fetch('User/repwd'))
            ->build();
    }


    public function add()
    {
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');

            if ($data['pwd'] != $data['pwd1']) {
                $this->error('密码不一致');
            }

            $data['status'] = DBCont::NORMAL_STATUS;
            $data['user_type'] = 'system';

            $user_model = D('User');
            $user_id = $user_model->newUser($data);
            if ($user_id === false) {
                $this->error($user_model->getError());
            } else {

                //插入用户组信息
                $this->_addRole($user_id);
                sysLogs('新增用户id:' . $user_id);

                $this->success(l('add') . l('success'), U(CONTROLLER_NAME . '/index'));
            }
        } else {
            // 使用FormBuilder快速建立表单页面。
            $role = new \Common\Model\RoleModel();
            $map['status'] = DBCont::NORMAL_STATUS;
            $role_list = $role->getRoleList($map);
            foreach ($role_list as $role) {
                $role_options[$role['id']] = $role['name'];
            }
            $builder = new \Qscmf\Builder\FormBuilder();
            $builder->setMetaTitle('新增用户') //设置页面标题
            ->setNID(5)
                ->setPostUrl(U('add'))    //设置表单提交地址
                ->addFormItem('nick_name', 'text', '用户名*')
                ->addFormItem('email', 'text', '电子邮箱*')
                ->addFormItem('telephone', 'text', '手机')
                ->addFormItem('pwd', 'password', '密码*')
                ->addFormItem('pwd1', 'password', '重复密码*')
                ->addFormItem('role', 'select', '用户组', '', $role_options)
                ->build();
        }
    }

    public function edit($id)
    {
        if (IS_POST) {
            parent::autoCheckToken();
            $user_id = I('post.id');
            $data = I('post.');
            $user_model = D('User');
            if (!$user_id) {
                E('缺少user_id');
            }

            $user_ent = $user_model->getOne($user_id);
            if (!$user_ent) {
                E('不存在用户');
            }

            //需要更新的fields
            $user_ent['nick_name'] = $data['nick_name'];
            $user_ent['email'] = $data['email'];
            $user_ent['telephone'] = $data['telephone'];
            $user_ent['portrait'] = $data['portrait'];

            if ($user_model->createSave($user_ent) === false) {
                $this->error($user_model->getError());
            } else {
                $this->_addRole($user_id);
                sysLogs('修改用户id:' . $user_id);
                $this->success('修改成功', U('index'));
            }
        } else {
            // 获取账号信息
            $info = D('User')->getOne($id);
            $role_user_ent = D('RoleUser')->getByUser_id($id);
            $info['role'] = $role_user_ent['role_id'];

            $role = new \Common\Model\RoleModel();
            $map['status'] = DBCont::NORMAL_STATUS;
            $role_list = $role->getRoleList($map);
            foreach ($role_list as $role) {
                $role_options[$role['id']] = $role['name'];
            }

            // 使用FormBuilder快速建立表单页面。
            $builder = new \Qscmf\Builder\FormBuilder();
            $builder->setMetaTitle('编辑用户')  // 设置页面标题
            ->setPostUrl(U('edit'))    // 设置表单提交地址
            ->setNID(5)
                ->addFormItem('id', 'hidden', 'ID')
                ->addFormItem('nick_name', 'text', '用户名*')
                ->addFormItem('email', 'text', '电子邮箱*')
                ->addFormItem('telephone', 'text', '手机')
                ->addFormItem('role', 'select', '用户组', '', $role_options)
                ->setFormData($info)
                ->build();
        }
    }

    //插入用户组信息
    private function _addRole($user_id)
    {
        $role_id = I('role');
        $role_user = D('RoleUser');
        $data_arr = array();
        $data_arr[] = array('role_id' => $role_id, 'user_id' => $user_id);
        $r = $role_user->where(array('user_id' => $user_id))->delete();
        if ($r === false) {
            $this->error($role_user->getError());
        }
        if (!empty($data_arr)) {
            $r = $role_user->addAll($data_arr);
            if ($r === false) {
                $this->error($role_user->getError());
            }
        }
    }

    //激活用户
    public function active()
    {
        $ids = I('ids');
        if (!$ids) {
            $this->error('请选择要激活的用户');
        }
        $user_model = D('User');
        $map['id'] = array('in', $ids);
        $r = $user_model->where($map)->setField('status', DBCont::NORMAL_STATUS);
        //设置默认分组
        $default_ent = D('DefaultRole')->find();
        if ($default_ent) {
            $user_ents = $user_model->where($map)->select();
            foreach ($user_ents as $v) {
                D('RoleUser')->where('user_id=' . $v['id'] . ' and role_id=' . $default_ent['role_id'])->delete();
                D('RoleUser')->add(array('role_id' => $default_ent['role_id'], 'user_id' => $v['id']));
            }
        }
        if ($r === false) {
            $this->error($user_model->getError());
        } else {
            sysLogs('用户id: ' . $ids . ' 激活');
            $this->success('激活成功', U(CONTROLLER_NAME . '/index'));
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
            sysLogs('用户id: ' . $ids . ' 禁用');
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
            sysLogs('用户id: ' . $ids . ' 启用');
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
            sysLogs('用户id: ' . $ids . ' 删除');
            $this->success('删除成功', U(MODULE_NAME . '/' . CONTROLLER_NAME . '/index'));
        }
    }

    public function repwd()
    {

        if (IS_POST) {
            parent::autoCheckToken();

            $user_model = new \Common\Model\UserModel();

            if (I('post.pwd') != I('post.pwd1')) {
                $this->error('密码不一致');
            }

            $user_ent = $user_model->getOne(I('post.id'));
            if (!$user_ent) {
                $this->error('用户不存在');
            }

            $r = $user_model->modifyPwdByAdmin(I('post.id'), I('post.pwd'));
            if ($r === false) {
                $this->error($user_model->getError());
            } else {
                syslogs('修改密码, 用户id:' . I('id'));
                $this->success('修改密码成功');
            }
        }
    }

    /**
     * 登陆者编辑自己的资料
     */
    public function editUser()
    {
        $id = session('auth_id');
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');
            if ($data['pwd'] != $data['pwd1']) {
                $this->error('密码不一致');
            }
            $user_model = D('User');
            $user_ent = $user_model->getOne($id);
            if (!$user_ent) {
                E('不存在用户');
            }
            $user_model->startTrans();
            try {
                $save = [
                    'id' => $id,
                    'nick_name' => $data['nick_name'],
                    'email' => $data['email'],
                    'telephone' => $data['telephone'],
                ];
                if ($user_model->createSave($save) === false) {
                    E($user_model->getError());
                }
                if (!empty($data['pwd'])) {
                    if ($user_model->modifyPwdByAdmin($id, $data['pwd']) === false) {
                        E($user_model->getError());
                    }
                }
                $user_model->commit();
            } catch (\Exception $e) {
                $user_model->rollback();
                $this->error($e->getMessage());
            }
            if (empty($data['referer'])) {
                $this->success('修改成功', U('admin/dashboard/index'));
            } else {
                $this->success('修改成功', $data['referer']);
            }
        } else {
            // 获取账号信息
            $info = D('User')->getOne($id);
            unset($info['pwd']);
            $info['referer'] = $_SERVER['HTTP_REFERER'];
            $builder = new \Qscmf\Builder\FormBuilder();
            $builder->setMetaTitle('编辑用户')  // 设置页面标题
            ->setPostUrl(U(''))
                ->addFormItem('nick_name', 'text', '用户名*')
                ->addFormItem('email', 'text', '电子邮箱')
                ->addFormItem('telephone', 'text', '手机')
                ->addFormItem('pwd', 'password', '密码')
                ->addFormItem('pwd1', 'password', '重复密码')
                ->addFormItem('referer', 'hidden', '跳转地址')
                ->setFormData($info)
                ->build();
        }
    }

    protected function buildPasswordModal(mixed $id)
    {
        $builder = new FormBuilder();
        $builder->setPostUrl(U('/admin/user/repwd'))
            ->addFormItem('pwd', 'password', '新密码')
            ->addFormItem('pwd1', 'password', '重复密码')
            ->addFormItem('id', 'hidden')
            ->setFormData(['id' => $id])
            ->setShowBtn(false);

        return (new \Qs\ModalButton\ModalButtonBuilder())
            ->bindFormBuilder($builder)
            ->setKeyboard(false)
            ->setBackdrop(false)
            ->setBodyHeight('200px')
            ->setDialogWidth('800px')
            ->setTitle('修改密码');
    }

}
