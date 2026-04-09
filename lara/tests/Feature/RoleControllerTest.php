<?php

namespace Lara\Tests\Feature;

use Illuminate\Support\Str;
use Illuminate\Database\Capsule\Manager as Capsule;
use Lara\Tests\TestCase;

class RoleControllerTest extends TestCase
{
    /**
     * 测试角色列表页
     */
    public function testRoleIndex()
    {
        $this->loginSuperAdmin();

        $content = $this->get('/Admin/Role/index');

        $this->assertTrue(Str::contains($content, '用户组列表') || Str::contains($content, '角色管理'), '应该包含用户组列表标题');
    }

    /**
     * 测试新增角色页面
     */
    public function testRoleAddPage()
    {
        $this->loginSuperAdmin();

        $content = $this->get('/Admin/Role/add');

        $this->assertTrue($content !== false, '新增角色页面应该可以访问');
    }

    /**
     * 测试编辑角色页面
     */
    public function testRoleEditPage()
    {
        $this->loginSuperAdmin();

        // 先创建一个测试角色
        $role = Capsule::table('role')->insertGetId([
            'name' => 'test_role_' . time(),
            'status' => 1,
            'remark' => '测试角色',
        ]);

        $content = $this->get('/Admin/Role/edit/id/' . $role);

        $this->assertTrue($content !== false, '编辑角色页面应该可以访问');

        // 清理
        Capsule::table('role')->where('id', $role)->delete();
    }

    /**
     * 测试新增角色功能
     */
    public function testRoleCreate()
    {
        $this->loginSuperAdmin();

        $name = 'test_role_' . time();
        $content = $this->post('/Admin/Role/add', [
            'name' => $name,
            'status' => 1,
            'remark' => '测试角色',
        ]);

        $this->assertTrue(Str::contains($content, '成功') || Str::contains($content, 'success'), '应该显示成功信息');

        // 清理
        Capsule::table('role')->where('name', $name)->delete();
    }

    /**
     * 测试禁用角色功能
     */
    public function testRoleForbid()
    {
        $this->loginSuperAdmin();

        // 先创建一个测试角色
        $role = Capsule::table('role')->insertGetId([
            'name' => 'test_forbid_' . time(),
            'status' => 1,
            'remark' => '测试禁用角色',
        ]);

        $content = $this->post('/Admin/Role/forbid', [
            'ids' => [$role],
        ]);

        $this->assertTrue(Str::contains($content, '成功') || Str::contains($content, 'success'), '应该显示成功信息');

        // 验证状态已更改
        $roleData = Capsule::table('role')->where('id', $role)->first();
        $this->assertEquals(0, $roleData->status, '角色状态应该变为禁用');

        // 清理
        Capsule::table('role')->where('id', $role)->delete();
    }

    /**
     * 测试启用角色功能
     */
    public function testRoleResume()
    {
        $this->loginSuperAdmin();

        // 先创建一个禁用的测试角色
        $role = Capsule::table('role')->insertGetId([
            'name' => 'test_resume_' . time(),
            'status' => 0,
            'remark' => '测试启用角色',
        ]);

        $content = $this->post('/Admin/Role/resume', [
            'ids' => [$role],
        ]);

        $this->assertTrue(Str::contains($content, '成功') || Str::contains($content, 'success'), '应该显示成功信息');

        // 验证状态已更改
        $roleData = Capsule::table('role')->where('id', $role)->first();
        $this->assertEquals(1, $roleData->status, '角色状态应该变为正常');

        // 清理
        Capsule::table('role')->where('id', $role)->delete();
    }

    /**
     * 测试删除角色功能
     */
    public function testRoleDelete()
    {
        $this->loginSuperAdmin();

        // 先创建一个测试角色
        $role = Capsule::table('role')->insertGetId([
            'name' => 'test_delete_' . time(),
            'status' => 1,
            'remark' => '测试删除角色',
        ]);

        $content = $this->post('/Admin/Role/delete', [
            'ids' => [$role],
        ]);

        $this->assertTrue(Str::contains($content, '成功') || Str::contains($content, 'success'), '应该显示成功信息');

        // 验证已删除
        $roleData = Capsule::table('role')->where('id', $role)->first();
        $this->assertNull($roleData, '角色应该已被删除');
    }

    /**
     * 清理测试数据
     */
    protected function tearDown(): void
    {
        Capsule::table('role')->where('name', 'like', 'test_role_%')->delete();
        Capsule::table('role')->where('name', 'like', 'test_forbid_%')->delete();
        Capsule::table('role')->where('name', 'like', 'test_resume_%')->delete();
        Capsule::table('role')->where('name', 'like', 'test_delete_%')->delete();
        parent::tearDown();
    }
}
