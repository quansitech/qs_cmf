<?php
/**
 * spec — 测试 Admin\Controller\UserController 的数据逻辑方法。
 *
 * 只覆盖纯 DB 逻辑的 protected 方法（applySearchConditions / saveUserRole /
 * getRoleOptions / getUserRolesMap）。public action（add/edit/forbid 等）依赖
 * ThinkPHP 的 error()/success() 的 qs_exit、IS_POST、AntdAdmin render，属集成测试范畴，
 * 不在此覆盖。
 *
 * 构造策略：QsController::__construct(?AuthStarter, ?BackendInitializer) 在参数为 null 时
 * 走容器兜底。bootstrap.php 已把两个协作者绑成无副作用 mock，故直接 new UserController()
 * 即可——控制器走真实构造（属性正常初始化），_initialize 的鉴权/菜单/Hook 副作用被 mock 吞掉。
 * 比反射跳过构造更干净，且控制器属性就绪。
 *
 * protected 方法仍需反射调用（这是 PHP 访问控制，不是 controller 构造问题）。
 */

use Admin\Controller\UserController;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Gy_Library\DBCont;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Collection;

describe('UserController 数据逻辑方法', function () {

    beforeAll(function () {
        // user/role/role_user 表结构由 bootstrap 的 loadCachedSchema() 从迁移缓存自动建好，
        // 无需在此 defineTables。修改迁移后跑 php artisan migrate 即自动刷新。
        // 被测的 4 个 protected 方法是纯 DB 逻辑，不依赖 IS_POST/sysLogs
        //（那些只在 _initialize/public action，已通过 mock 协作者跳过）。
        // 协作者的无副作用 mock 由 bootstrap 全局绑定到容器，直接 new UserController() 即可。
    });

    beforeEach(function () {
        cleanTables(['user', 'role', 'role_user']);
        resetSqlBuffer();
    });

    describe('applySearchConditions()', function () {

        // 应用搜索条件后执行查询，返回捕获到的 SQL（含绑定值）。
        // 注意：applySearchConditions 内部可能触发 role_user 子查询（role 分支），
        // 因此必须包在 captureSql 内，才能同时捕获子查询与主查询。
        $captureAfterApply = function (array $getData) {
            return captureSql(function () use ($getData) {
                // 容器默认绑定无副作用协作者，零参构造即可跳过 _initialize 副作用
                $controller = new UserController();
                $query = User::query();
                callProtected($controller, 'applySearchConditions', [$query, $getData]);
                $query->get();
            });
        };

        it('数字 id 加精确等值条件', function () use ($captureAfterApply) {
            $sqls = $captureAfterApply(['id' => '5']);

            expect($sqls)->toHaveLength(1);
            expect($sqls[0]['sql'])->toContain('where "id" = ?');
            expect($sqls[0]['bindings'])->toBe([5]); // 注意被强转为 int
        });

        it('非数字 id（如 5a）不加 id 条件', function () use ($captureAfterApply) {
            $sqls = $captureAfterApply(['id' => '5a']);

            expect($sqls)->toHaveLength(1);
            expect($sqls[0]['sql'])->not->toContain('"id" = ?');
            expect($sqls[0]['bindings'])->toBe([]);
        });

        it('nick_name 加模糊条件，两端加 %', function () use ($captureAfterApply) {
            $sqls = $captureAfterApply(['nick_name' => 'tider']);

            expect($sqls[0]['sql'])->toContain('where "nick_name" like ?');
            expect($sqls[0]['bindings'])->toBe(['%tider%']);
        });

        it('email 加模糊条件', function () use ($captureAfterApply) {
            $sqls = $captureAfterApply(['email' => 'x@y.com']);

            expect($sqls[0]['sql'])->toContain('where "email" like ?');
            expect($sqls[0]['bindings'])->toBe(['%x@y.com%']);
        });

        it('telephone 加模糊条件', function () use ($captureAfterApply) {
            $sqls = $captureAfterApply(['telephone' => '138']);

            expect($sqls[0]['sql'])->toContain('where "telephone" like ?');
            expect($sqls[0]['bindings'])->toBe(['%138%']);
        });

        it('role 条件先查 role_user 拿 user_ids，再 whereIn', function () use ($captureAfterApply) {
            // 预置 role_user 关联
            Capsule::table('role_user')->insert([
                ['user_id' => 10, 'role_id' => 3],
                ['user_id' => 11, 'role_id' => 3],
                ['user_id' => 12, 'role_id' => 4], // 不属于 role 3
            ]);

            $sqls = $captureAfterApply(['role' => '3']);

            // 含一条查 role_user 的子查询（按 role_id 取 user_id）
            $roleUserSqls = array_filter($sqls, fn($s) => stripos($s['sql'], 'from "role_user"') !== false);
            expect($roleUserSqls)->not->toBeEmpty();
            $ru = array_values($roleUserSqls)[0];
            expect($ru['sql'])->toContain('"role_id" = ?');
            expect($ru['bindings'])->toBe([3]);

            // 含一条 user 表的 whereIn（绑定值是命中的 user_id）。
            // 注意：role_user.user_id 在真实 schema 是 varchar，故绑定值为字符串。
            $userSqls = array_filter($sqls, fn($s) => stripos($s['sql'], 'from "user"') !== false);
            expect($userSqls)->not->toBeEmpty();
            $u = array_values($userSqls)[0];
            expect($u['sql'])->toContain('"id" in');
            expect($u['bindings'])->toBe(['10', '11']);
        });

        it('空参数不加任何 where', function () use ($captureAfterApply) {
            $sqls = $captureAfterApply([]);

            expect($sqls[0]['sql'])->not->toContain('where');
            expect($sqls[0]['bindings'])->toBe([]);
        });

        it('多字段组合时 where 链式叠加', function () use ($captureAfterApply) {
            $sqls = $captureAfterApply(['id' => '5', 'nick_name' => 'ab', 'email' => 'c@d']);

            expect($sqls[0]['sql'])->toContain('"id" = ?');
            expect($sqls[0]['sql'])->toContain('"nick_name" like ?');
            expect($sqls[0]['sql'])->toContain('"email" like ?');
            expect($sqls[0]['bindings'])->toBe([5, '%ab%', '%c@d%']);
        });
    });

    describe('saveUserRole()', function () {

        it('传入新角色时删除旧关联并建立新关联', function () {
            Capsule::table('role_user')->insert([
                ['user_id' => 1, 'role_id' => 2], // 旧
                ['user_id' => 1, 'role_id' => 7], // 旧（会被删）
            ]);

            $controller = new UserController();
            callProtected($controller, 'saveUserRole', [1, 3]);

            // 该 user 只剩新关联（真实 role_user 无 id 主键，仅 user_id/role_id 两列）
            $rows = Capsule::table('role_user')->where('user_id', 1)->get();
            expect($rows->count())->toBe(1);
            expect((int)$rows->first()->role_id)->toBe(3);
        });

        it('传入 null 角色时只删不建', function () {
            Capsule::table('role_user')->insert(['user_id' => 2, 'role_id' => 5]);

            $controller = new UserController();
            callProtected($controller, 'saveUserRole', [2, null]);

            expect(Capsule::table('role_user')->where('user_id', 2)->count())->toBe(0);
        });

        it('原无关联时传入新角色直接建立', function () {
            $controller = new UserController();
            callProtected($controller, 'saveUserRole', [3, 9]);

            $rows = Capsule::table('role_user')->where('user_id', 3)->get();
            expect($rows->count())->toBe(1);
            expect((int)$rows->first()->role_id)->toBe(9);
        });

        it('只影响目标 user，不动其它用户关联', function () {
            Capsule::table('role_user')->insert([
                ['user_id' => 4, 'role_id' => 1],
                ['user_id' => 5, 'role_id' => 1], // 其它用户，不应被影响
            ]);

            $controller = new UserController();
            callProtected($controller, 'saveUserRole', [4, 2]);

            expect(Capsule::table('role_user')->where('user_id', 4)->pluck('role_id')->all())->toBe([2]);
            expect(Capsule::table('role_user')->where('user_id', 5)->pluck('role_id')->all())->toBe([1]);
        });
    });

    describe('getRoleOptions()', function () {

        it('只返回正常状态（NORMAL_STATUS）的角色，映射为 [id => name]', function () {
            Capsule::table('role')->insert([
                ['id' => 1, 'name' => '管理员', 'status' => DBCont::NORMAL_STATUS],
                ['id' => 2, 'name' => '编辑',   'status' => DBCont::NORMAL_STATUS],
                ['id' => 3, 'name' => '已禁用', 'status' => DBCont::FORBIDDEN_STATUS],
            ]);

            $controller = new UserController();
            $options = callProtected($controller, 'getRoleOptions');

            expect($options)->toBe([1 => '管理员', 2 => '编辑']);
        });

        it('无角色时返回空数组', function () {
            $controller = new UserController();
            expect(callProtected($controller, 'getRoleOptions'))->toBe([]);
        });
    });

    describe('getUserRolesMap()', function () {

        // 给定 user_ids 集合，返回 [user_id => '角色名1,角色名2'] 映射
        $getMap = function (array $userIds): array {
            $controller = new UserController();
            return callProtected($controller, 'getUserRolesMap', [collect($userIds)]);
        };

        it('多角色用户返回逗号拼接的角色名', function () use ($getMap) {
            Capsule::table('role')->insert([
                ['id' => 1, 'name' => '管理员', 'status' => DBCont::NORMAL_STATUS],
                ['id' => 2, 'name' => '编辑',   'status' => DBCont::NORMAL_STATUS],
            ]);
            Capsule::table('role_user')->insert([
                ['user_id' => 10, 'role_id' => 1],
                ['user_id' => 10, 'role_id' => 2],
                ['user_id' => 11, 'role_id' => 2],
            ]);

            $map = $getMap([10, 11]);

            expect($map[10])->toBe('管理员,编辑');
            expect($map[11])->toBe('编辑');
        });

        it('过滤掉非正常状态的角色', function () use ($getMap) {
            Capsule::table('role')->insert([
                ['id' => 1, 'name' => '管理员', 'status' => DBCont::NORMAL_STATUS],
                ['id' => 2, 'name' => '已禁用', 'status' => DBCont::FORBIDDEN_STATUS],
            ]);
            Capsule::table('role_user')->insert([
                ['user_id' => 20, 'role_id' => 1],
                ['user_id' => 20, 'role_id' => 2], // FORBIDDEN，应被过滤
            ]);

            $map = $getMap([20]);

            expect($map[20])->toBe('管理员');
        });

        it('空 user_ids 集合返回空数组', function () use ($getMap) {
            expect($getMap([]))->toBe([]);
        });

        it('无角色关联的用户不出现在结果中', function () use ($getMap) {
            Capsule::table('role')->insert([
                ['id' => 1, 'name' => '管理员', 'status' => DBCont::NORMAL_STATUS],
            ]);
            Capsule::table('role_user')->insert([
                ['user_id' => 30, 'role_id' => 1],
                // user 31 无任何关联
            ]);

            $map = $getMap([30, 31]);

            expect($map)->toContainKey(30);
            expect($map)->not->toContainKey(31);
        });
    });
});
