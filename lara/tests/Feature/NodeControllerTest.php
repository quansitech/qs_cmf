<?php

namespace Lara\Tests\Feature;

use Illuminate\Support\Str;
use Illuminate\Database\Capsule\Manager as Capsule;
use Lara\Tests\TestCase;

class NodeControllerTest extends TestCase
{
    /**
     * 测试节点列表页
     */
    public function testNodeIndex()
    {
        $this->loginSuperAdmin();

        $content = $this->get('/Admin/Node/index');

        // v15 后台为 Inertia 页面，断言 page 的 metaTitle
        $page = $this->inertiaPage($content);
        $this->assertNotNull($page, '节点列表页应为 Inertia 页面');
        $this->assertEquals('Admin/Tabs', $page['component']);
        $this->assertEquals('节点管理', $page['props']['layoutProps']['metaTitle']);
    }

    /**
     * 测试节点列表页（禁用状态）
     */
    public function testNodeIndexForbidden()
    {
        $this->loginSuperAdmin();

        $content = $this->get('/Admin/Node/index/status/0');

        $this->assertTrue($content !== false, '禁用状态页应该可以访问');
    }

    /**
     * 测试新增节点页面
     */
    public function testNodeAddPage()
    {
        $this->loginSuperAdmin();

        $content = $this->get('/Admin/Node/add');

        $this->assertTrue($content !== false, '新增节点页面应该可以访问');
    }

    /**
     * 测试编辑节点页面
     */
    public function testNodeEditPage()
    {
        $this->loginSuperAdmin();

        // 先创建一个测试节点
        $node = Capsule::table('node')->insertGetId([
            'name' => 'TestNode_' . time(),
            'title' => '测试节点',
            'sort' => 0,
            'status' => 1,
            'pid' => 0,
            'level' => 1,
        ]);

        $content = $this->get('/Admin/Node/edit/id/' . $node);

        $this->assertTrue($content !== false, '编辑节点页面应该可以访问');

        // 清理
        Capsule::table('node')->where('id', $node)->delete();
    }

    /**
     * 测试新增节点功能
     */
    public function testNodeCreate()
    {
        $this->loginSuperAdmin();

        $name = 'TestNode_' . time();
        $this->post('/Admin/Node/add', [
            '__hash__' => $this->getTpToken('/admin/node/add', false),
            'name' => $name,
            'title' => '测试节点',
            'sort' => 0,
            'status' => 1,
            'pid' => 0,
            'level' => 1,
            'module' => 'Admin',
            'controller' => 'Test',
        ]);

        // v15 新增成功由前端处理（无成功文案），改断言记录已落库
        $this->assertDatabaseHas('node', ['name' => $name]);

        // 清理
        Capsule::table('node')->where('name', $name)->delete();
    }

    /**
     * 测试禁用节点功能
     */
    public function testNodeForbid()
    {
        $this->loginSuperAdmin();

        // 先创建一个测试节点
        $node = Capsule::table('node')->insertGetId([
            'name' => 'TestForbid_' . time(),
            'title' => '测试节点禁用',
            'sort' => 0,
            'status' => 1,
            'pid' => 0,
            'level' => 1,
        ]);

        $content = $this->post('/Admin/Node/forbid', [
            'ids' => [$node],
        ]);

        $this->assertTrue(Str::contains($content, '成功') || Str::contains($content, 'success'), '应该显示成功信息');

        // 验证状态已更改
        $nodeData = Capsule::table('node')->where('id', $node)->first();
        $this->assertEquals(0, $nodeData->status, '节点状态应该变为禁用');

        // 清理
        Capsule::table('node')->where('id', $node)->delete();
    }

    /**
     * 测试启用节点功能
     */
    public function testNodeResume()
    {
        $this->loginSuperAdmin();

        // 先创建一个禁用的测试节点
        $node = Capsule::table('node')->insertGetId([
            'name' => 'TestResume_' . time(),
            'title' => '测试节点启用',
            'sort' => 0,
            'status' => 0,
            'pid' => 0,
            'level' => 1,
        ]);

        $content = $this->post('/Admin/Node/resume', [
            'ids' => [$node],
        ]);

        $this->assertTrue(Str::contains($content, '成功') || Str::contains($content, 'success'), '应该显示成功信息');

        // 验证状态已更改
        $nodeData = Capsule::table('node')->where('id', $node)->first();
        $this->assertEquals(1, $nodeData->status, '节点状态应该变为正常');

        // 清理
        Capsule::table('node')->where('id', $node)->delete();
    }

    /**
     * 测试删除节点功能
     */
    public function testNodeDelete()
    {
        $this->loginSuperAdmin();

        // 先创建一个测试节点
        $node = Capsule::table('node')->insertGetId([
            'name' => 'TestDelete_' . time(),
            'title' => '测试节点删除',
            'sort' => 0,
            'status' => 1,
            'pid' => 0,
            'level' => 1,
        ]);

        $content = $this->post('/Admin/Node/delete', [
            'ids' => [$node],
        ]);

        $this->assertTrue(Str::contains($content, '成功') || Str::contains($content, 'success'), '应该显示成功信息');

        // 验证已删除
        $nodeData = Capsule::table('node')->where('id', $node)->first();
        $this->assertNull($nodeData, '节点应该已被删除');
    }

    /**
     * 清理测试数据
     */
    protected function tearDown(): void
    {
        Capsule::table('node')->where('name', 'like', 'TestNode_%')->delete();
        Capsule::table('node')->where('name', 'like', 'TestForbid_%')->delete();
        Capsule::table('node')->where('name', 'like', 'TestResume_%')->delete();
        Capsule::table('node')->where('name', 'like', 'TestDelete_%')->delete();
        parent::tearDown();
    }
}
