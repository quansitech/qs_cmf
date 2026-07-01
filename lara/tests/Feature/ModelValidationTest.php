<?php

namespace Lara\Tests\Feature;

use App\Models\User;
use app\Models\Syslogs;
use Illuminate\Database\Capsule\Manager as Capsule;
use Lara\Tests\TestCase;

class ModelValidationTest extends TestCase
{
    /**
     * 测试 watson/validating 验证功能
     */
    public function testUserValidation()
    {
        // 测试 1: 创建用户 - 缺少必填字段 email
        $user = new User();
        $user->nick_name = 'test_user';
        $user->pwd = '123456';
        $user->telephone = '13800138000';
        // 故意不设置 email

        $result = $user->save();

        $this->assertFalse($result, '验证应该失败');
        $this->assertTrue($user->getErrors()->has('email'), '应该有 email 字段的错误');

        // 测试 2: 手机号格式错误
        $user = new User();
        $user->nick_name = 'test_user2';
        $user->pwd = '123456';
        $user->telephone = '12345'; // 错误格式
        $user->email = 'test@example.com';
        $user->status = 1;

        $result = $user->save();

        $this->assertFalse($result, '手机号格式验证应该失败');
        $this->assertTrue($user->getErrors()->has('telephone'), '应该有 telephone 字段的错误');

        // 测试 3: 邮箱格式错误
        $user = new User();
        $user->nick_name = 'test_user3';
        $user->pwd = '123456';
        $user->telephone = '13800138001';
        $user->email = 'invalid-email'; // 错误格式
        $user->status = 1;

        $result = $user->save();

        $this->assertFalse($result, '邮箱格式验证应该失败');
        $this->assertTrue($user->getErrors()->has('email'), '应该有 email 字段的错误');

        // 注：密码长度验证（min:6|max:12）在 v15 已从 User 模型 rules 移除
        // （pwd 仅 required），故移除对应的长度验证测试段。

        // 测试 4: 更新用户时的 unique 验证（应该允许相同的 nick_name）
        // 先创建一个用户
        $user = new User();
        $user->nick_name = 'test_unique_user';
        $user->pwd = '123456';
        $user->telephone = '13800138003';
        $user->email = 'unique@example.com';
        $user->status = 1;
        $user->register_date = time();
        $result = $user->save();

        $this->assertTrue($result, '创建有效用户应该成功');

        // 更新同一个用户，nick_name 应该不冲突
        $user->telephone = '13800138004';
        $result = $user->save();

        $this->assertTrue($result, '更新时 unique 验证应该排除当前记录');

        // 清理
        $user->delete();
    }

    /**
     * 测试创建有效用户
     */
    public function testCreateValidUser()
    {
        $user = new User();
        $user->nick_name = 'valid_test_user';
        $user->pwd = '123456';
        $user->telephone = '13900139999';
        $user->email = 'valid_test@example.com';
        $user->status = 1;
        $user->register_date = time();

        $result = $user->save();

        $this->assertTrue($result, '创建有效用户应该成功');
        $this->assertNotEmpty($user->id, '用户应该有 ID');
        $this->assertEquals('valid_test_user', $user->nick_name);

        // 清理
        $user->delete();
    }

    /**
     * 测试删除前验证 - not_in 规则
     */
    public function testDeleteValidationNotIn()
    {
        // 尝试删除 ID=1 的超级管理员（应该失败）
        $user = User::find(1);

        if ($user) {
            $result = $user->delete();

            $this->assertFalse($result, '删除 ID=1 的用户应该失败');
            $this->assertTrue($user->getErrors()->has('id'), '应该有 id 字段的错误');
            $this->assertStringContainsString('超级管理员', $user->getErrors()->first());
        } else {
            $this->markTestSkipped('未找到 ID=1 的用户');
        }
    }

    /**
     * 测试删除前验证 - no_related 规则
     */
    public function testDeleteValidationNoRelated()
    {
        // 创建测试用户
        $user = new User();
        $user->nick_name = 'test_with_logs';
        $user->pwd = '123456';
        $user->telephone = '13900139998';
        $user->email = 'test_logs@example.com';
        $user->status = 1;
        $user->register_date = time();
        $user->save();

        $userId = $user->id;

        // 为该用户添加系统日志
        Capsule::table('syslogs')->insert([
            'userid' => (string)$userId,
            'modulename' => 'Test',
            'actionname' => 'test_action',
            'opname' => 'delete_validation_test',
            'message' => '测试删除验证',
            'userip' => '127.0.0.1',
            'create_time' => time(),
        ]);

        // 重新加载用户
        $user = User::find($userId);

        // 尝试删除该用户（应该失败）
        $result = $user->delete();

        $this->assertFalse($result, '有系统日志的用户删除应该失败');
        $this->assertTrue($user->getErrors()->has('id'), '应该有验证错误');
        $this->assertStringContainsString('系统日记', $user->getErrors()->first());

        // 清理：先删除日志，再删除用户
        Capsule::table('syslogs')->where('userid', (string)$userId)->delete();
        $user->delete();
    }

    /**
     * 测试删除无关联数据的用户（应该成功）
     */
    public function testDeleteUserWithoutRelations()
    {
        // 创建测试用户
        $user = new User();
        $user->nick_name = 'delete_test_user';
        $user->pwd = '123456';
        $user->telephone = '13900139997';
        $user->email = 'delete_test@example.com';
        $user->status = 1;
        $user->register_date = time();
        $user->save();

        $userId = $user->id;

        // 删除用户（应该成功）
        $result = $user->delete();

        $this->assertTrue($result, '删除无关联的用户应该成功');

        // 验证用户已被删除
        $deletedUser = User::find($userId);
        $this->assertNull($deletedUser, '用户应该已被删除');
    }

    /**
     * 测试联动删除功能
     */
    public function testCascadeDelete()
    {
        // 创建测试用户
        $user = new User();
        $user->nick_name = 'cascade_test_user';
        $user->pwd = '123456';
        $user->telephone = '13900138888';
        $user->email = 'cascade_test@example.com';
        $user->status = 1;
        $user->register_date = time();
        $user->save();

        $userId = $user->id;

        // 为用户添加角色关联
        Capsule::table('role_user')->insert([
            'user_id' => $userId,
            'role_id' => 2,
        ]);

        $roleUserCount = Capsule::table('role_user')
            ->where('user_id', $userId)
            ->count();

        $this->assertEquals(1, $roleUserCount, '应该有 1 条 role_user 记录');

        // 删除用户（应该自动删除 role_user 关联）
        $result = $user->delete();

        $this->assertTrue($result, '用户删除应该成功');

        // 检查 role_user 是否也被删除
        $remainingCount = Capsule::table('role_user')
            ->where('user_id', $userId)
            ->count();

        $this->assertEquals(0, $remainingCount, 'role_user 记录应该被自动删除');
    }

    /**
     * 测试删除验证失败时不执行联动删除
     */
    public function testCascadeDeleteNotExecutedOnValidationFailure()
    {
        // 创建测试用户
        $user = new User();
        $user->nick_name = 'no_cascade_test';
        $user->pwd = '123456';
        $user->telephone = '13900138887';
        $user->email = 'no_cascade@example.com';
        $user->status = 1;
        $user->register_date = time();
        $user->save();

        $userId = $user->id;

        // 为用户添加角色关联
        Capsule::table('role_user')->insert([
            'user_id' => $userId,
            'role_id' => 2,
        ]);

        // 添加系统日志（使删除验证失败）
        Capsule::table('syslogs')->insert([
            'userid' => (string)$userId,
            'modulename' => 'Test',
            'actionname' => 'test',
            'opname' => 'test',
            'message' => 'test',
            'userip' => '127.0.0.1',
            'create_time' => time(),
        ]);

        // 重新加载用户
        $user = User::find($userId);

        // 尝试删除（应该失败，不执行联动删除）
        $result = $user->delete();

        $this->assertFalse($result, '有日志的用户删除应该失败');

        // 验证 role_user 记录仍然存在（未执行联动删除）
        $roleUserCount = Capsule::table('role_user')
            ->where('user_id', $userId)
            ->count();

        $this->assertEquals(1, $roleUserCount, '验证失败时不应该执行联动删除');

        // 清理
        Capsule::table('syslogs')->where('userid', (string)$userId)->delete();
        Capsule::table('role_user')->where('user_id', $userId)->delete();
        $user->delete();
    }

    /**
     * 清理测试数据
     */
    protected function tearDown(): void
    {
        // 清理测试创建的用户
        $testUsers = User::where('nick_name', 'like', 'test_%')
            ->orWhere('nick_name', 'like', '%_test_%')
            ->orWhere('nick_name', 'like', 'valid_%')
            ->orWhere('nick_name', 'like', 'cascade_%')
            ->orWhere('nick_name', 'like', 'delete_%')
            ->get();

        foreach ($testUsers as $user) {
            // 删除相关的 syslogs
            Capsule::table('syslogs')->where('userid', (string)$user->id)->delete();
            // 删除相关的 role_user
            Capsule::table('role_user')->where('user_id', $user->id)->delete();
            // 删除用户
            $user->delete();
        }

        parent::tearDown();
    }
}
