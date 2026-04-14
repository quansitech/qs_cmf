<?php

namespace Lara\Tests\Feature;

use App\Models\Config;
use App\Models\Menu;
use App\Models\Node;
use App\Models\Role;
use App\Models\Syslogs;
use App\Models\Access;
use Illuminate\Database\Capsule\Manager as Capsule;
use Lara\Tests\TestCase;

class ModelQueryTest extends TestCase
{
    /**
     * 测试 Config 模型查询方法
     */
    public function testConfigModelMethods()
    {
        // 测试 count
        $count = Config::count();
        $this->assertIsInt($count, 'count 应该返回整数');

        // 测试分页查询
        $list = Config::offset(0)->limit(10)->get()->toArray();
        $this->assertIsArray($list, '分页查询应该返回数组');

        // 测试 getOne
        $first = Config::first();
        if ($first) {
            $found = Config::getOne($first->id);
            $this->assertNotNull($found, 'getOne 应该返回数据');
            $this->assertEquals($first->id, $found['id']);
        }
    }

    /**
     * 测试 Menu 模型查询方法
     */
    public function testMenuModelMethods()
    {
        // 测试 getMenuList
        $list = Menu::getMenuList();
        $this->assertIsArray($list, 'getMenuList 应该返回数组');

        // 测试 getMenuListGroupByType
        $grouped = Menu::getMenuListGroupByType();
        $this->assertIsArray($grouped, 'getMenuListGroupByType 应该返回数组');

        // 测试 getParentOptions
        $options = Menu::getParentOptions('id', 'title');
        $this->assertIsArray($options, 'getParentOptions 应该返回数组');

        // 测试 getOne
        $first = Menu::first();
        if ($first) {
            $found = Menu::getOne($first->id);
            $this->assertNotNull($found, 'getOne 应该返回数据');
            $this->assertEquals($first->id, $found['id']);
        }
    }

    /**
     * 测试 Node 模型查询方法
     */
    public function testNodeModelMethods()
    {
        // 测试 getNodeList
        $list = Node::getNodeList([]);
        $this->assertIsArray($list, 'getNodeList 应该返回数组');

        // 测试 getModuleList
        $modules = Node::getModuleList();
        $this->assertIsArray($modules, 'getModuleList 应该返回数组');

        // 测试 count
        $count = Node::count();
        $this->assertIsInt($count, 'count 应该返回整数');

        // 测试分页查询
        $page = Node::offset(0)->limit(10)->get()->toArray();
        $this->assertIsArray($page, '分页查询应该返回数组');

        // 测试 getOne
        $first = Node::first();
        if ($first) {
            $found = Node::getOne($first->id);
            $this->assertNotNull($found, 'getOne 应该返回数据');
            $this->assertEquals($first->id, $found['id']);
        }
    }

    /**
     * 测试 Role 模型查询方法
     */
    public function testRoleModelMethods()
    {
        // 测试 getRoleList
        $list = Role::getRoleList([]);
        $this->assertIsArray($list, 'getRoleList 应该返回数组');

        // 测试 count
        $count = Role::count();
        $this->assertIsInt($count, 'count 应该返回整数');

        // 测试分页查询
        $page = Role::offset(0)->limit(10)->get()->toArray();
        $this->assertIsArray($page, '分页查询应该返回数组');

        // 测试 getOne
        $first = Role::first();
        if ($first) {
            $found = Role::getOne($first->id);
            $this->assertNotNull($found, 'getOne 应该返回数据');
            $this->assertEquals($first->id, $found['id']);
        }
    }

    /**
     * 测试 Access 模型查询方法
     */
    public function testAccessModelMethods()
    {
        // 测试 getAccessList
        $list = Access::getAccessList([]);
        $this->assertIsArray($list, 'getAccessList 应该返回数组');

        // 测试 delAccess（用新的测试数据）
        Capsule::table('access')->insert([
            'role_id' => 999,
            'node_id' => 999,
            'level' => 1,
            'module' => 'test',
        ]);
        $deleted = Access::delAccess(['role_id' => 999, 'node_id' => 999]);
        $this->assertEquals(1, $deleted, 'delAccess 应该删除 1 条记录');
    }

    /**
     * 测试 Syslogs 模型查询方法
     */
    public function testSyslogsModelMethods()
    {
        // 测试 count
        $count = Syslogs::count();
        $this->assertIsInt($count, 'count 应该返回整数');

        // 测试分页查询
        $page = Syslogs::offset(0)->limit(10)->get()->toArray();
        $this->assertIsArray($page, '分页查询应该返回数组');

        // 测试全量查询
        $list = Syslogs::all()->toArray();
        $this->assertIsArray($list, '全量查询应该返回数组');
    }

    /**
     * 测试 Menu 模型批量插入和删除
     */
    public function testMenuCrud()
    {
        // 创建测试菜单
        $menu = Menu::create([
            'title' => '测试菜单',
            'name' => 'test_menu',
            'sort' => 0,
            'type' => 1,
            'status' => 1,
            'pid' => 0,
            'level' => 1,
        ]);

        $this->assertNotNull($menu->id, '创建菜单应该有 ID');

        // 更新
        $menu->title = '测试菜单更新';
        $result = $menu->save();
        $this->assertTrue($result, '更新应该成功');

        // 删除
        $id = $menu->id;
        $deleted = Menu::destroy($id);
        $this->assertEquals(1, $deleted, '删除应该返回 1');

        // 验证删除
        $found = Menu::find($id);
        $this->assertNull($found, '删除后应该找不到数据');
    }

    /**
     * 测试 Node 模型批量插入和删除
     */
    public function testNodeCrud()
    {
        // 创建测试节点
        $node = Node::create([
            'name' => 'TestNode',
            'title' => '测试节点',
            'sort' => 0,
            'status' => 1,
            'pid' => 0,
            'level' => 1,
        ]);

        $this->assertNotNull($node->id, '创建节点应该有 ID');

        // 更新
        $node->title = '测试节点更新';
        $result = $node->save();
        $this->assertTrue($result, '更新应该成功');

        // 删除
        $id = $node->id;
        $deleted = Node::destroy($id);
        $this->assertEquals(1, $deleted, '删除应该返回 1');
    }

    /**
     * 测试 Role 模型批量插入和删除
     */
    public function testRoleCrud()
    {
        // 创建测试角色
        $role = Role::create([
            'name' => 'test_role_' . time(),
            'status' => 1,
            'remark' => '测试角色',
        ]);

        $this->assertNotNull($role->id, '创建角色应该有 ID');

        // 更新
        $role->remark = '测试角色更新';
        $result = $role->save();
        $this->assertTrue($result, '更新应该成功');

        // 删除
        $id = $role->id;
        $deleted = Role::destroy($id);
        $this->assertEquals(1, $deleted, '删除应该返回 1');
    }

    /**
     * 测试 Node::getNode 条件查询
     */
    public function testNodeGetNode()
    {
        // 创建一个模块节点
        $module = Node::create([
            'name' => 'TestModule_' . time(),
            'title' => '测试模块',
            'sort' => 0,
            'status' => 1,
            'pid' => 0,
            'level' => 1,
        ]);

        // 测试 getNode 单条件
        $found = Node::getNode(['id' => $module->id]);
        $this->assertNotNull($found, 'getNode 应该返回数据');
        $this->assertEquals($module->id, $found['id']);

        // 测试 getNode 多条件
        $found2 = Node::getNode([
            'id' => $module->id,
            'name' => $module->name,
            'level' => 1,
        ]);
        $this->assertNotNull($found2, 'getNode 多条件应该返回数据');

        // 清理
        Node::destroy($module->id);
    }

    /**
     * 清理测试数据
     */
    protected function tearDown(): void
    {
        // 清理测试创建的菜单
        Capsule::table('menu')->where('name', 'like', 'test_%')->delete();

        // 清理测试创建的节点
        Capsule::table('node')->where('name', 'like', 'TestNode%')->delete();
        Capsule::table('node')->where('name', 'like', 'TestModule_%')->delete();

        // 清理测试创建的角色
        Capsule::table('role')->where('name', 'like', 'test_role_%')->delete();

        parent::tearDown();
    }
}
