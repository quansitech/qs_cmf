<?php

namespace Admin\Controller;

use Gy_Library\DBCont;
use Gy_Library\GyListController;
use App\Models\User;
use App\Models\Role;
use App\Models\RoleUser;
use AntdAdmin\Component\Tabs;
use AntdAdmin\Component\Table;
use AntdAdmin\Component\Table\Pagination;
use AntdAdmin\Component\Modal\Modal;
use AntdAdmin\Component\Form;
use AntdAdmin\Component\ColumnType\RuleType\Required;
use AntdAdmin\Component\ColumnType\RuleType\Pattern;
use AntdAdmin\Component\ColumnType\RuleType\Min;
use AntdAdmin\Component\ColumnType\RuleType\Max;
use AntdAdmin\Component\ColumnType\RuleType\Type;

class UserController extends GyListController
{

    public function index()
    {
        // 获取搜索参数
        $get_data = I("get.");

        // Tab 导航 - 直接渲染每个 tab 的内容
        $user_status_list = DBCont::getUserStatusList();
        $tabs = new Tabs();

        foreach ($user_status_list as $key => $val) {
            // 为每个状态创建一个 Table
            $table = $this->createUserListTable($key, $get_data);
            $tabs->addTab('tab_' . $key, $val, $table);
        }

        $tabs->setDefaultActiveKey('tab_' . array_key_first($user_status_list));
        $this->setActiveNid(getNid(MODULE_NAME, CONTROLLER_NAME, 'Index'));
        $tabs->setMetaTitle('账号列表')
             ->render();
    }

    /**
     * 创建用户列表 Table 组件
     * @param int $status 用户状态
     * @param array $get_data 搜索参数
     * @return Table
     */
    private function createUserListTable($status, array $get_data = [])
    {
        $per_page = C('ADMIN_PER_PAGE_NUM', null, false);

        // 数据查询
        $query = User::where('status', $status);

        // 应用搜索条件
        $this->applySearchConditions($query, $get_data);

        $users = $query->orderBy('register_date', 'desc')->paginate($per_page);

        $page = new Pagination($users->currentPage(), $users->perPage(), $users->total());

        $role_options = $this->getRoleOptions();

        // 批量获取角色信息，避免 N+1 查询
        $user_ids = collect($users->items())->pluck('id');
        $role_id_map = [];
        if (!$user_ids->isEmpty()) {
            $role_id_map = RoleUser::whereIn('user_id', $user_ids)
                ->pluck('role_id', 'user_id')
                ->toArray();
        }

        // 数据处理 - 使用角色ID（select组件通过valueEnum自动显示名称）
        $data_list = collect($users->items())->map(function($item) use ($role_id_map) {
            $data = $item->toArray();
            $data['role'] = $role_id_map[$data['id']] ?? '';
            return $data;
        })->all();

        $table = new Table();
        $table->setMetaTitle('账号列表')
            ->setSearch(true)
            ->actions(function(Table\ActionsContainer $actions){
                $actions->button('添加')->modal(
                    (new Modal())->setUrl(U('add'))->setWidth(1080)->setTitle('新增用户')
                );
            })
            ->columns(function (Table\ColumnsContainer $container) use ($role_options){
                $container->text('id', 'ID');
                $container->text('nick_name', '用户名');
                $container->text('email', '邮箱');
                $container->text('telephone', '手机');
                $container->select('role', '用户组')->setValueEnum($role_options);
                $container->action('', '操作')->actions(function(Table\ColumnType\ActionsContainer $actions){
                    $actions->link('编辑')
                        ->modal((new Modal())->setUrl(U('edit', ['id'=>'__id__']))->setWidth(1080)->setTitle('编辑用户'));
                    $actions->link('修改密码')
                        ->modal((new Modal())->setUrl(U('repwd', ['id'=>'__id__']))->setWidth(800)->setTitle('修改密码'));
                });
            })
            ->setDataSource($data_list)
            ->setPagination($page);

        return $table;
    }

    /**
     * 应用搜索查询条件
     * @param \Illuminate\Database\Eloquent\Builder $query 查询构建器
     * @param array $get_data 前端 GET 参数
     */
    protected function applySearchConditions($query, array $get_data): void
    {
        // ID 精确搜索
        if (!empty($get_data['id'])) {
            $id = trim($get_data['id']);
            if (ctype_digit($id)) {
                $query->where('id', '=', (int)$id);
            }
        }

        // 用户名模糊搜索
        if (!empty($get_data['nick_name'])) {
            $query->where('nick_name', 'like', '%' . trim($get_data['nick_name']) . '%');
        }

        // 邮箱模糊搜索
        if (!empty($get_data['email'])) {
            $query->where('email', 'like', '%' . trim($get_data['email']) . '%');
        }

        // 手机号模糊搜索
        if (!empty($get_data['telephone'])) {
            $query->where('telephone', 'like', '%' . trim($get_data['telephone']) . '%');
        }

        // 用户组搜索
        if (!empty($get_data['role'])) {
            $role_id = (int)$get_data['role'];
            $user_ids_with_role = RoleUser::where('role_id', $role_id)->pluck('user_id')->toArray();
            $query->whereIn('id', $user_ids_with_role);
        }
    }

    /**
     * 批量获取用户角色映射（避免 N+1 查询）
     * @param \Illuminate\Support\Collection $user_ids
     * @return array [user_id => role_names]
     */
    protected function getUserRolesMap($user_ids): array
    {
        if ($user_ids->isEmpty()) {
            return [];
        }

        // 批量查询用户角色关联
        $role_users = RoleUser::whereIn('user_id', $user_ids)
            ->get()
            ->groupBy('user_id');

        // 获取所有角色ID
        $role_ids = $role_users->flatten()->pluck('role_id')->unique();

        if ($role_ids->isEmpty()) {
            return [];
        }

        // 批量查询角色名称
        $roles = Role::whereIn('id', $role_ids)
            ->where('status', DBCont::NORMAL_STATUS)
            ->pluck('name', 'id');

        // 构建用户ID到角色名称的映射
        $role_map = [];
        foreach ($role_users as $user_id => $items) {
            $role_names = [];
            foreach ($items as $role_user) {
                $role_id = $role_user->role_id;
                if (isset($roles[$role_id])) {
                    $role_names[] = $roles[$role_id];
                }
            }
            $role_map[$user_id] = implode(',', $role_names);
        }

        return $role_map;
    }

    /**
     * 新增用户
     */
    public function add()
    {
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');

            // 验证密码一致性
            if ($data['pwd'] !== $data['pwd1']) {
                $this->error('两次密码不一致');
            }

            // 设置默认值
            $data['status'] = DBCont::NORMAL_STATUS;
            $data['user_type'] = 'system';
            $data['register_date'] = time();

            // 使用 User 模型验证并保存
            $user = new User();
            $user->nick_name = $data['nick_name'];
            $user->email = $data['email'];
            $user->telephone = $data['telephone'];
            $user->pwd = $user->hashPwd($data['pwd']);
            $user->status = $data['status'];
            $user->register_date = $data['register_date'];

            if (!$user->save()) {
                $errors = $user->getErrors();
                $err_msg = is_array($errors) ? implode('; ', $errors) : (string)$errors;
                $this->error('新增用户失败: ' . $err_msg);
            }

            // 保存用户角色关联
            $this->saveUserRole($user->id, $data['role']);

            sysLogs('新增用户id: ' . $user->id);
            $this->success('新增成功');
        } else {
            $form = new Form();
            $form->setMetaTitle('新增用户')
                ->setSubmitRequest('post', U('add'));
            $this->buildUserForm($form)->render();
        }
    }

    /**
     * 编辑用户
     */
    public function edit()
    {
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');
            $user_id = $data['id'] ?? null;

            if (!$user_id) {
                $this->error('缺少用户ID');
            }

            // 查找用户
            $user = User::find($user_id);
            if (!$user) {
                $this->error('用户不存在');
            }

            // 更新字段
            $user->nick_name = $data['nick_name'];
            $user->email = $data['email'];
            $user->telephone = $data['telephone'];

            if (!$user->save()) {
                $errors = $user->getErrors();
                $err_msg = is_array($errors) ? implode('; ', $errors) : (string)$errors;
                $this->error('更新用户失败: ' . $err_msg);
            }

            // 更新用户角色关联
            $this->saveUserRole($user_id, $data['role']);

            sysLogs('修改用户id: ' . $user_id);
            $this->success('修改成功');
        } else {
            $id = I('id');
            if (!$id) {
                $this->error('缺少用户ID');
            }

            // 获取用户信息
            $user = User::find($id);
            if (!$user) {
                $this->error('用户不存在');
            }

            // 获取用户角色
            $role_ids = RoleUser::getRoleIdsByUserId($id);
            $user->role = $role_ids[0] ?? null;

            $form = new Form();
            $form->setMetaTitle('编辑用户')
                ->setSubmitRequest('post', U('edit'));
            $this->buildUserForm($form, $user)->render();
        }
    }

    /**
     * 修改密码
     */
    public function repwd()
    {
        if (IS_POST) {
            parent::autoCheckToken();
            $data = I('post.');

            // 验证密码一致性
            if ($data['pwd'] !== $data['pwd1']) {
                $this->error('两次密码不一致');
            }

            $user_id = $data['id'] ?? null;
            if (!$user_id) {
                $this->error('缺少用户ID');
            }

            // 查找用户
            $user = User::find($user_id);
            if (!$user) {
                $this->error('用户不存在');
            }

            // 更新密码
            $user->pwd = $user->hashPwd($data['pwd']);

            if (!$user->save()) {
                $errors = $user->getErrors();
                $err_msg = is_array($errors) ? implode('; ', $errors) : (string)$errors;
                $this->error('修改密码失败: ' . $err_msg);
            }

            sysLogs('修改密码, 用户id: ' . $user_id);
            $this->success('修改密码成功');
        } else {
            $user_id = I('id');
            if (!$user_id) {
                $this->error('缺少用户ID');
            }

            $form = new Form();
            $form->setMetaTitle('修改密码')
                ->setSubmitRequest('post', U('repwd'));
            $this->buildPasswordForm($form, $user_id)->render();
        }
    }

    public function forbid()
    {
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要禁用的数据');
        }
        $r = User::whereIn('id', is_array($ids) ? $ids : explode(',', $ids))->update(['status' => DBCont::FORBIDDEN_STATUS]);
        if($r !== false){
            sysLogs('用户id: ' . (is_array($ids) ? implode(',', $ids) : $ids) . ' 禁用');
            $this->success('禁用成功');
        }
        else{
            $this->error('禁用失败');
        }
    }

    public function resume()
    {
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要启用的数据');
        }
        $r = User::whereIn('id', is_array($ids) ? $ids : explode(',', $ids))->update(['status' => DBCont::NORMAL_STATUS]);
        if($r !== false){
            sysLogs('用户id: ' . (is_array($ids) ? implode(',', $ids) : $ids) . ' 启用');
            $this->success('启用成功');
        }
        else{
            $this->error('启用失败');
        }
    }

    public function delete()
    {
        $ids = I('ids');
        if(!$ids){
            $this->error('请选择要删除的数据');
        }
        $r = User::destroy(is_array($ids) ? $ids : explode(',', $ids));
        if($r === false){
            $this->error('删除失败');
        }
        else{
            sysLogs('用户id: ' . $ids . ' 删除');
            $this->success('删除成功');
        }
    }

    /**
     * 构建用户表单
     * @param Form $form 表单对象
     * @param User|null $user 用户对象（编辑时传入）
     * @return Form
     */
    protected function buildUserForm(Form $form, ?User $user = null): Form
    {
        $role_options = $this->getRoleOptions();
        $is_edit = $user !== null;

        $form->actions(function (Form\ActionsContainer $actions) {
            $actions->button('提交')->submit()->setProps(['type' => 'primary']);
            $actions->button('重置')->reset();
        });

        $form->columns(function (Form\ColumnsContainer $columns) use ($role_options, $is_edit, $user) {
            $columns->text('nick_name', '用户名')
                ->addRule(new Required('用户名不能为空'));

            $columns->text('email', '电子邮箱')
                ->addRule(new Required('邮箱不能为空'))
                ->addRule(new Type('email', '邮箱格式不正确'));

            $columns->text('telephone', '手机')
                ->addRule(new Required('手机号不能为空'))
                ->addRule(new Pattern('^1\\d{10}$', '手机号码格式不正确'));

            if (!$is_edit) {
                // 新增时显示密码字段
                $columns->password('pwd', '密码*')
                    ->addRule(new Required('密码不能为空'))
                    ->addRule(new Min('string', 6, '密码长度至少6位'))
                    ->addRule(new Max('string', 12, '密码长度最多12位'));

                $columns->password('pwd1', '重复密码*')
                    ->addRule(new Required('请再次输入密码'))
                    ->setTips('需与密码一致');
            }

            $columns->select('role', '用户组')
                ->setValueEnum($role_options);

            if ($is_edit) {
                // 编辑时添加隐藏的 ID 字段
                $columns->text('id', '')->hideInForm();
            }
        });

        // 如果是编辑模式，设置初始值
        if ($is_edit) {
            $form->setInitialValues([
                'id' => $user->id,
                'nick_name' => $user->nick_name,
                'email' => $user->email,
                'telephone' => $user->telephone,
                'role' => $user->role ?? null,
            ]);
        }

        return $form;
    }

    /**
     * 构建密码修改表单
     * @param Form $form 表单对象
     * @param int $user_id 用户ID
     * @return Form
     */
    protected function buildPasswordForm(Form $form, int $user_id): Form
    {
        $form->actions(function (Form\ActionsContainer $actions) {
            $actions->button('提交')->submit()->setProps(['type' => 'primary']);
            $actions->button('重置')->reset();
        });

        $form->columns(function (Form\ColumnsContainer $columns) use ($user_id) {
            $columns->password('pwd', '新密码')
                ->addRule(new Required('新密码不能为空'))
                ->addRule(new Min('string', 6, '密码长度至少6位'))
                ->addRule(new Max('string', 12, '密码长度最多12位'));

            $columns->password('pwd1', '重复密码')
                ->addRule(new Required('请再次输入密码'))
                ->setTips('需与新密码一致');

            $columns->text('id', '')->hideInForm();
        });

        // 设置初始值
        $form->setInitialValues(['id' => $user_id]);

        return $form;
    }

    /**
     * 获取角色选项列表
     * @return array
     */
    protected function getRoleOptions(): array
    {
        $role = new Role();
        $role_list = $role->getRoleList(['status' => DBCont::NORMAL_STATUS]);

        $role_options = [];
        foreach ($role_list as $role) {
            $role_options[$role['id']] = $role['name'];
        }

        return $role_options;
    }

    /**
     * 保存用户角色关联
     * @param int $user_id 用户ID
     * @param int $role_id 角色ID
     * @return void
     */
    protected function saveUserRole(int $user_id, ?int $role_id): void
    {
        // 删除原有的角色关联
        RoleUser::where('user_id', $user_id)->delete();

        // 角色为空时跳过
        if (empty($role_id)) {
            return;
        }

        // 创建新的角色关联
        $role_user = new RoleUser();
        $role_user->user_id = $user_id;
        $role_user->role_id = $role_id;
        $role_user->save();
    }

}
