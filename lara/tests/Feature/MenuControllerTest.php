<?php

namespace Lara\Tests\Feature;

use Illuminate\Support\Str;
use Illuminate\Database\Capsule\Manager as Capsule;
use Lara\Tests\TestCase;

class MenuControllerTest extends TestCase
{
    /**
     * 测试菜单列表页
     */
    public function testMenuIndex()
    {
        $this->loginSuperAdmin();

        $content = $this->get('/Admin/Menu/index');

        $this->assertTrue(Str::contains($content, '菜单管理'), '应该包含菜单管理标题');
    }

    /**
     * 测试菜单列表页（禁用状态）
     */
    public function testMenuIndexForbidden()
    {
        $this->loginSuperAdmin();

        $content = $this->get('/Admin/Menu/index/status/0');

        $this->assertTrue($content !== false, '禁用状态页应该可以访问');
    }

    /**
     * 测试新增菜单页面
     */
    public function testMenuAddPage()
    {
        $this->loginSuperAdmin();

        $content = $this->get('/Admin/Menu/add');

        $this->assertTrue($content !== false, '新增菜单页面应该可以访问');
    }

    /**
     * 测试编辑菜单页面
     */
    public function testMenuEditPage()
    {
        $this->loginSuperAdmin();

        // 先创建一个测试菜单
        $menu = Capsule::table('menu')->insertGetId([
            'title' => '测试菜单_' . time(),
            'name' => 'test_menu_' . time(),
            'sort' => 0,
            'type' => 1,
            'status' => 1,
            'pid' => 0,
            'level' => 1,
        ]);

        $content = $this->get('/Admin/Menu/edit/id/' . $menu);

        $this->assertTrue($content !== false, '编辑菜单页面应该可以访问');

        // 清理
        Capsule::table('menu')->where('id', $menu)->delete();
    }

    /**
     * 测试新增菜单功能
     */
    public function testMenuCreate()
    {
        $this->loginSuperAdmin();

        $title = '测试菜单_' . time();
        $content = $this->post('/Admin/Menu/add', [
            'title' => $title,
            'sort' => 0,
            'type' => 1,
            'status' => 1,
            'pid' => 0,
            'level' => 1,
        ]);

        $this->assertTrue(Str::contains($content, '成功') || Str::contains($content, 'success'), '应该显示成功信息');

        // 清理
        Capsule::table('menu')->where('title', $title)->delete();
    }

    /**
     * 测试禁用菜单功能
     */
    public function testMenuForbid()
    {
        $this->loginSuperAdmin();

        // 先创建一个测试菜单
        $menu = Capsule::table('menu')->insertGetId([
            'title' => '测试菜单_forbid_' . time(),
            'name' => 'test_forbid_' . time(),
            'sort' => 0,
            'type' => 1,
            'status' => 1,
            'pid' => 0,
            'level' => 1,
        ]);

        $content = $this->post('/Admin/Menu/forbid', [
            'ids' => [$menu],
        ]);

        $this->assertTrue(Str::contains($content, '成功') || Str::contains($content, 'success'), '应该显示成功信息');

        // 验证状态已更改
        $menuData = Capsule::table('menu')->where('id', $menu)->first();
        $this->assertEquals(0, $menuData->status, '菜单状态应该变为禁用');

        // 清理
        Capsule::table('menu')->where('id', $menu)->delete();
    }

    /**
     * 测试启用菜单功能
     */
    public function testMenuResume()
    {
        $this->loginSuperAdmin();

        // 先创建一个禁用的测试菜单
        $menu = Capsule::table('menu')->insertGetId([
            'title' => '测试菜单_resume_' . time(),
            'name' => 'test_resume_' . time(),
            'sort' => 0,
            'type' => 1,
            'status' => 0,
            'pid' => 0,
            'level' => 1,
        ]);

        $content = $this->post('/Admin/Menu/resume', [
            'ids' => [$menu],
        ]);

        $this->assertTrue(Str::contains($content, '成功') || Str::contains($content, 'success'), '应该显示成功信息');

        // 验证状态已更改
        $menuData = Capsule::table('menu')->where('id', $menu)->first();
        $this->assertEquals(1, $menuData->status, '菜单状态应该变为正常');

        // 清理
        Capsule::table('menu')->where('id', $menu)->delete();
    }

    /**
     * 测试删除菜单功能
     */
    public function testMenuDelete()
    {
        $this->loginSuperAdmin();

        // 先创建一个测试菜单
        $menu = Capsule::table('menu')->insertGetId([
            'title' => '测试菜单_delete_' . time(),
            'name' => 'test_delete_' . time(),
            'sort' => 0,
            'type' => 1,
            'status' => 1,
            'pid' => 0,
            'level' => 1,
        ]);

        $content = $this->post('/Admin/Menu/delete', [
            'ids' => [$menu],
        ]);

        $this->assertTrue(Str::contains($content, '成功') || Str::contains($content, 'success'), '应该显示成功信息');

        // 验证已删除
        $menuData = Capsule::table('menu')->where('id', $menu)->first();
        $this->assertNull($menuData, '菜单应该已被删除');
    }

    /**
     * 清理测试数据
     */
    protected function tearDown(): void
    {
        Capsule::table('menu')->where('name', 'like', 'test_%')->delete();
        parent::tearDown();
    }
}
