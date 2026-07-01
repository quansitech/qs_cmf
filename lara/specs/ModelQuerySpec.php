<?php
/**
 * spec — 业务模型查询方法单元测试。
 *
 * 从 PHPUnit Feature/ModelQueryTest 迁移而来。这类「直接调模型查询方法」的测试
 * 属于单元测试范畴（不走 ThinkPHP 请求流程），按测试分层原则应使用 Kahlan：
 * - PHPUnit Feature 负责端到端测试（$this->get('/Admin/...') 走完整请求）；
 * - Kahlan 负责纯 DB 逻辑的单元测试（内存 SQLite + schema 缓存，mock 数据 + 断言）。
 *
 * 业务表结构由 bootstrap.php 的 loadCachedSchema() 从迁移生成的 schema_cache.php
 * 自动加载，spec 内只需 cleanTables() 隔离数据 + 造测试数据。
 *
 * 注意：ThinkPHP 运行时常量（如 __APP__，由 Dispatcher 阶段定义）在 Kahlan 不存在，
 * Menu::getMenuList() 会经 U() 用到 __APP__，故在 beforeAll 预定义。
 */

use App\Models\Access;
use App\Models\Config;
use App\Models\Menu;
use App\Models\Node;
use App\Models\Role;
use App\Models\Syslogs;
use Illuminate\Database\Capsule\Manager as Capsule;

describe('业务模型查询方法', function () {

    // __APP__ / URL_PATHINFO_DEPR 等 ThinkPHP 运行时常量与配置已由 bootstrap.php 预定义。

    // 用例间清空数据，保证隔离
    beforeEach(function () {
        cleanTables(['menu', 'node', 'role', 'access', 'syslogs', 'config']);
        resetSqlBuffer();
    });

    describe('Config 模型', function () {

        it('支持 count / 分页查询 / 按 id 查询', function () {
            Capsule::table('config')->insert([
                ['name' => 'site_name', 'value' => '测试站点', 'remark' => ''],
                ['name' => 'site_url', 'value' => 'http://example.com', 'remark' => ''],
            ]);

            expect(Config::count())->toBe(2);
            expect(is_array(Config::offset(0)->limit(10)->get()->toArray()))->toBe(true);

            $first = Config::first();
            $found = Config::find($first->id);
            expect($found)->not->toBeNull();
            expect($found->id)->toBe($first->id);
        });
    });

    describe('Menu 模型', function () {

        it('getMenuList 返回菜单数组', function () {
            // url 留空以避开 U() 的 ThinkPHP 运行时常量依赖（__APP__/__ROOT__ 等），
            // 单元测试聚焦查询逻辑本身；url 转换属 ThinkPHP 运行时行为，应由端到端测试覆盖。
            Capsule::table('menu')->insert([
                'title' => '系统', 'status' => 1, 'icon' => '', 'url' => '',
                'type' => 'backend_menu', 'sort' => 0, 'pid' => 0, 'level' => 1,
            ]);

            $list = Menu::getMenuList();
            expect(is_array($list))->toBe(true);
            expect(count($list))->toBe(1);
            expect($list[0]['title'])->toBe('系统');
        });

        it('getMenuListGroupByType 按类型分组', function () {
            Capsule::table('menu')->insert([
                ['title' => '菜单1', 'status' => 1, 'icon' => '', 'url' => '', 'type' => 'backend_menu', 'sort' => 0, 'pid' => 0, 'level' => 1],
                ['title' => '菜单2', 'status' => 1, 'icon' => '', 'url' => '', 'type' => 'backend_menu', 'sort' => 0, 'pid' => 0, 'level' => 1],
            ]);

            $grouped = Menu::getMenuListGroupByType();
            expect(is_array($grouped))->toBe(true);
            expect($grouped)->toContainKey('backend_menu');
            expect(count($grouped['backend_menu']))->toBe(2);
        });

        it('getParentOptions 返回层级选项', function () {
            Capsule::table('menu')->insert([
                ['title' => '父菜单', 'status' => 1, 'icon' => '', 'url' => '', 'type' => 'backend_menu', 'sort' => 0, 'pid' => 0, 'level' => 1],
            ]);

            $options = Menu::getParentOptions('id', 'title');
            expect(is_array($options))->toBe(true);
        });

        it('getOne 按 id 返回数组', function () {
            $id = Capsule::table('menu')->insertGetId([
                'title' => '单查菜单', 'status' => 1, 'icon' => '', 'url' => '', 'type' => 'backend_menu', 'sort' => 0, 'pid' => 0, 'level' => 1,
            ]);

            $found = Menu::getOne($id);
            expect($found)->not->toBeNull();
            expect($found['id'])->toBe($id);

            // 不存在的 id 返回 null
            expect(Menu::getOne(99999))->toBeNull();
        });

        it('CRUD：创建 / 更新 / 删除', function () {
            $menu = Menu::create([
                'title' => '测试菜单', 'icon' => '', 'sort' => 0,
                'type' => 1, 'status' => 1, 'pid' => 0, 'level' => 1,
            ]);
            expect($menu->id)->not->toBeNull();

            $menu->title = '测试菜单更新';
            expect($menu->save())->toBe(true);

            $id = $menu->id;
            expect(Menu::destroy($id))->toBe(1);
            expect(Menu::find($id))->toBeNull();
        });
    });

    describe('Node 模型', function () {

        it('getNodeList / getModuleList / count / 分页查询', function () {
            Capsule::table('node')->insert([
                ['name' => 'Admin', 'title' => '后台', 'status' => 1, 'sort' => 0, 'pid' => 0, 'level' => 1],
                ['name' => 'Admin/Index', 'title' => '首页', 'status' => 1, 'sort' => 0, 'pid' => 1, 'level' => 2],
            ]);

            expect(is_array(Node::getNodeList([])))->toBe(true);
            expect(is_array(Node::getModuleList()))->toBe(true);
            expect(Node::count())->toBe(2);
            expect(is_array(Node::offset(0)->limit(10)->get()->toArray()))->toBe(true);
        });

        it('getOne 按 id 返回数组', function () {
            $id = Capsule::table('node')->insertGetId([
                'name' => 'TestNode', 'title' => '测试节点', 'status' => 1, 'sort' => 0, 'pid' => 0, 'level' => 1,
            ]);

            $found = Node::getOne($id);
            expect($found)->not->toBeNull();
            expect($found['id'])->toBe($id);
        });

        it('getNode 条件查询（单条件 / 多条件）', function () {
            $module = Node::create([
                'name' => 'TestModule', 'title' => '测试模块', 'sort' => 0,
                'status' => 1, 'pid' => 0, 'level' => 1,
            ]);

            $found = Node::getNode(['id' => $module->id]);
            expect($found)->not->toBeNull();
            expect($found['id'])->toBe($module->id);

            $found2 = Node::getNode(['id' => $module->id, 'name' => $module->name, 'level' => 1]);
            expect($found2)->not->toBeNull();
        });

        it('CRUD：创建 / 更新 / 删除', function () {
            $node = Node::create([
                'name' => 'TestNode', 'title' => '测试节点', 'sort' => 0,
                'status' => 1, 'pid' => 0, 'level' => 1,
            ]);
            expect($node->id)->not->toBeNull();

            $node->title = '测试节点更新';
            expect($node->save())->toBe(true);

            expect(Node::destroy($node->id))->toBe(1);
        });
    });

    describe('Role 模型', function () {

        it('getRoleList / count / 分页查询', function () {
            Capsule::table('role')->insert([
                ['name' => '管理员', 'status' => 1, 'remark' => ''],
                ['name' => '编辑', 'status' => 1, 'remark' => ''],
            ]);

            expect(is_array(Role::getRoleList([])))->toBe(true);
            expect(Role::count())->toBe(2);
            expect(is_array(Role::offset(0)->limit(10)->get()->toArray()))->toBe(true);
        });

        it('getOne 按 id 返回数组', function () {
            $id = Capsule::table('role')->insertGetId([
                'name' => '单查角色', 'status' => 1, 'remark' => '',
            ]);

            $found = Role::getOne($id);
            expect($found)->not->toBeNull();
            expect($found['id'])->toBe($id);
        });

        it('CRUD：创建 / 更新 / 删除', function () {
            $role = Role::create([
                'name' => 'test_role', 'status' => 1, 'remark' => '测试角色',
            ]);
            expect($role->id)->not->toBeNull();

            $role->remark = '测试角色更新';
            expect($role->save())->toBe(true);

            expect(Role::destroy($role->id))->toBe(1);
        });
    });

    describe('Access 模型', function () {

        it('getAccessList 返回数组', function () {
            Capsule::table('access')->insert([
                'role_id' => 1, 'node_id' => 1, 'level' => 1, 'module' => 'Admin',
            ]);

            expect(is_array(Access::getAccessList([])))->toBe(true);
        });

        it('delAccess 按条件删除', function () {
            Capsule::table('access')->insert([
                'role_id' => 999, 'node_id' => 999, 'level' => 1, 'module' => 'test',
            ]);

            $deleted = Access::delAccess(['role_id' => 999, 'node_id' => 999]);
            expect($deleted)->toBe(1);
        });
    });

    describe('Syslogs 模型', function () {

        it('count / 分页查询 / 全量查询', function () {
            Capsule::table('syslogs')->insert([
                ['userid' => '1', 'message' => '操作1', 'userip' => '127.0.0.1', 'create_time' => time()],
                ['userid' => '1', 'message' => '操作2', 'userip' => '127.0.0.1', 'create_time' => time()],
            ]);

            expect(Syslogs::count())->toBe(2);
            expect(is_array(Syslogs::offset(0)->limit(10)->get()->toArray()))->toBe(true);
            expect(is_array(Syslogs::all()->toArray()))->toBe(true);
        });
    });
});
