<?php
namespace Home\Controller;

use App\Models\User;

/**
 * 测试控制器 - 仅允许 CLI 模式运行
 */
class TestController extends \Gy_Library\GyController{

    public function _initialize(){
        parent::_initialize();

        // 只允许 CLI 模式运行
        if (!IS_CLI) {
            echo "此控制器仅允许在 CLI 模式下运行\n";
            exit;
        }
    }

    /**
     * 测试 watson/validating 验证功能
     * 运行命令: php /path/to/tp.php /Home/Test/validation
     */
    public function validation(){
        echo "\n=== 测试 watson/validating 验证功能 ===\n\n";

        // 测试 1: 创建用户 - 缺少必填字段
        echo "测试 1: 创建用户（缺少 email）\n";
        echo "----------------------------------------\n";
        try {
            $user = new User();
            $user->nick_name = 'test_user';
            $user->pwd = '123456';
            $user->telephone = '13800138000';
            // 故意不设置 email
            $r = $user->save();
            dd($r, $user->getErrors());
            echo "❌ 验证未生效（应该失败）\n";
        } catch (\Watson\Validating\ValidationException $e) {
            echo "✅ 验证成功拦截\n";
            echo "错误信息: " . $e->getErrors()->first() . "\n";
        } catch (\Exception $e) {
            echo "⚠️  其他异常: " . $e->getMessage() . "\n";
            echo "堆栈跟踪:\n";
            foreach ($e->getTrace() as $i => $trace) {
                if ($i > 5) break; // 只显示前6层
                echo "  #" . $i . " " . (isset($trace['file']) ? $trace['file'] . ':' . $trace['line'] : '') . "\n";
                if (isset($trace['function'])) {
                    echo "      " . (isset($trace['class']) ? $trace['class'] . '::' : '') . $trace['function'] . "()\n";
                }
            }
        }

        echo "\n";

        // 测试 2: 手机号格式验证
        echo "测试 2: 手机号格式错误\n";
        echo "----------------------------------------\n";
        try {
            $user = new User();
            $user->nick_name = 'test_user2';
            $user->pwd = '123456';
            $user->telephone = '12345'; // 错误格式
            $user->email = 'test@example.com';
            $user->status = 1;
            $user->save();
            echo "❌ 验证未生效（应该失败）\n";
        } catch (\Watson\Validating\ValidationException $e) {
            echo "✅ 验证成功拦截\n";
            echo "错误信息: " . $e->getErrors()->first() . "\n";
        } catch (\Exception $e) {
            echo "⚠️  其他异常: " . $e->getMessage() . "\n";
        }

        echo "\n";

        // 测试 3: 邮箱格式验证
        echo "测试 3: 邮箱格式错误\n";
        echo "----------------------------------------\n";
        try {
            $user = new User();
            $user->nick_name = 'test_user3';
            $user->pwd = '123456';
            $user->telephone = '13800138000';
            $user->email = 'invalid-email'; // 错误格式
            $user->status = 1;
            $user->save();
            echo "❌ 验证未生效（应该失败）\n";
        } catch (\Watson\Validating\ValidationException $e) {
            echo "✅ 验证成功拦截\n";
            echo "错误信息: " . $e->getErrors()->first() . "\n";
        } catch (\Exception $e) {
            echo "⚠️  其他异常: " . $e->getMessage() . "\n";
        }

        echo "\n";

        // 测试 4: 密码长度验证
        echo "测试 4: 密码长度不足\n";
        echo "----------------------------------------\n";
        try {
            $user = new User();
            $user->nick_name = 'test_user4';
            $user->pwd = '123'; // 长度不足6位
            $user->telephone = '13800138000';
            $user->email = 'test4@example.com';
            $user->status = 1;
            $user->save();

            echo "❌ 验证未生效（应该失败）\n";
        } catch (\Watson\Validating\ValidationException $e) {
            echo "✅ 验证成功拦截\n";
            echo "错误信息: " . $e->getErrors()->first() . "\n";
        } catch (\Exception $e) {
            echo "⚠️  其他异常: " . $e->getMessage() . "\n";
        }

        echo "\n";

        // 测试 5: 更新用户（验证 unique 自动排除当前 ID）
        echo "测试 5: 更新用户时的 unique 验证\n";
        echo "----------------------------------------\n";
        try {
            // 先创建一个测试用户
            $user = new User();
            $user->nick_name = 'original_user_' . time();
            $user->pwd = '123456';
            $user->telephone = '13900139000';
            $user->email = 'original_' . time() . '@example.com';
            $user->status = 1;
            $user->save();

            $userId = $user->id;
            echo "创建测试用户，ID: {$userId}\n";

            // 尝试更新同一个用户的 nick_name 为自己（应该成功）
            $user->nick_name = $user->nick_name; // 相同名称
            $user->telephone = '13900139001'; // 修改手机号
            $user->save();

            echo "✅ unique 验证正确：更新自身时不会触发 unique 错误\n";

            // 清理测试数据
            $user->delete();
            echo "已清理测试数据\n";

        } catch (\Watson\Validating\ValidationException $e) {
            echo "❌ unique 验证错误: " . $e->getErrors()->first() . "\n";
        } catch (\Exception $e) {
            echo "⚠️  其他异常: " . $e->getMessage() . "\n";
        }

        echo "\n";

        // 测试 6: 创建一个有效的用户（应该成功）
        echo "测试 6: 创建有效用户（应该成功）\n";
        echo "----------------------------------------\n";
        try {
            $user = new User();
            $user->nick_name = 'valid_user_' . time();
            $user->pwd = '123456';
            $user->telephone = '13800138001';
            $user->email = 'valid_' . time() . '@example.com';
            $user->status = 1;
            $user->save();

            echo "✅ 验证通过，用户创建成功，ID: {$user->id}\n";

            // 清理测试数据
            $user->delete();
            echo "已清理测试数据\n";

        } catch (\Watson\Validating\ValidationException $e) {
            echo "❌ 验证失败: " . $e->getErrors()->first() . "\n";
        } catch (\Exception $e) {
            echo "⚠️  其他异常: " . $e->getMessage() . "\n";
        }

        echo "\n=== 测试完成 ===\n\n";
    }

    /**
     * 快速测试：只测试核心功能
     * 运行命令: php /path/to/tp.php /Home/Test/quick
     */
    public function quick(){
        echo "\n=== 快速测试 watson/validating ===\n\n";

        // 测试：验证必填字段
        echo "测试：验证必填字段\n";
        try {
            $user = new User();
            $user->save(); // 不设置任何字段
            echo "❌ 验证未生效\n";
        } catch (\Watson\Validating\ValidationException $e) {
            echo "✅ 验证成功拦截\n";
            echo "错误信息:\n";
            foreach ($e->getErrors()->all() as $error) {
                echo "  - {$error}\n";
            }
        }

        echo "\n=== 快速测试完成 ===\n\n";
    }

    /**
     * 调试：检查 ValidatingTrait 是否正确加载
     * 运行命令: php /path/to/tp.php /Home/Test/debug
     */
    public function debug(){
        echo "\n=== 调试 watson/validating ===\n\n";

        $user = new User();

        // 1. 检查 Trait 是否存在
        echo "1. 检查 Trait:\n";
        $traits = class_uses($user);
        if (isset($traits['Watson\\Validating\\ValidatingTrait'])) {
            echo "   ✅ ValidatingTrait 已加载\n";
        } else {
            echo "   ❌ ValidatingTrait 未加载\n";
            echo "   已加载的 Traits:\n";
            foreach ($traits as $trait => $traitName) {
                echo "     - {$trait}\n";
            }
        }

        // 2. 检查验证规则
        echo "\n2. 检查验证规则:\n";
        $rules = $user->getRules();
        echo "   规则数量: " . count($rules) . "\n";
        foreach ($rules as $field => $rule) {
            echo "   - {$field}: {$rule}\n";
        }

        // 3. 检查验证开关
        echo "\n3. 检查验证开关:\n";
        echo "   validating: " . ($user->getValidating() ? '启用' : '禁用') . "\n";
        echo "   throwValidationExceptions: " . ($user->getThrowValidationExceptions() ? '是' : '否') . "\n";

        // 4. 检查模型事件
        echo "\n4. 检查模型观察者:\n";
        $dispatcher = $user->getEventDispatcher();
        if ($dispatcher) {
            echo "   ✅ 事件调度器存在\n";

            // 获取所有监听器
            $eventName = 'eloquent.saving: ' . get_class($user);
            $listeners = $dispatcher->getListeners($eventName);
            echo "   saving 事件监听器数量: " . count($listeners) . "\n";
            if (empty($listeners)) {
                echo "   ⚠️  没有 saving 事件监听器，验证可能不会触发\n";
            } else {
                echo "   监听器详情:\n";
                foreach ($listeners as $listener) {
                    if (is_array($listener) && isset($listener[0])) {
                        echo "     - " . get_class($listener[0]) . "\n";
                    } else {
                        echo "     - " . print_r($listener, true) . "\n";
                    }
                }
            }
        } else {
            echo "   ❌ 事件调度器不存在\n";
        }

        // 5. 手动触发验证测试
        echo "\n5. 手动触发验证测试:\n";
        $testUser = new User();
        // 不设置任何字段
        $isValid = $testUser->isValid();
        echo "   验证结果: " . ($isValid ? '通过' : '失败') . "\n";

        if (!$isValid) {
            echo "   错误信息:\n";
            foreach ($testUser->getErrors()->all() as $error) {
                echo "     - {$error}\n";
            }
        }

        echo "\n=== 调试完成 ===\n\n";
    }

    /**
     * 测试删除验证功能
     * 运行命令: php /path/to/tp.php /Home/Test/deleteValidation
     */
    public function deleteValidation(){
        echo "\n=== 测试删除验证功能 ===\n\n";

        // 测试 1: 尝试删除 ID=1 的超级管理员（应该失败）
        echo "测试 1: 尝试删除 ID=1 的超级管理员\n";
        echo "----------------------------------------\n";
        $user = User::find(1);
        if (!$user) {
            echo "⚠️  未找到 ID=1 的用户\n";
        } else {
            $result = $user->delete();
            if ($result === false) {
                echo "✅ 删除验证成功拦截\n";
                echo "错误信息: " . $user->getErrors()->first() . "\n";
            } else {
                echo "❌ 删除成功（应该失败）\n";
            }
        }

        echo "\n";

        // 测试 2: 尝试删除有系统日志的用户（应该失败）
        echo "测试 2: 尝试删除有系统日志的用户 (no_related 验证)\n";
        echo "----------------------------------------\n";
        try {
            // 创建测试用户
            $user = new User();
            $user->nick_name = 'test_with_logs';
            $user->pwd = '123456';
            $user->telephone = '13900139999';
            $user->email = 'test_with_logs@example.com';
            $user->status = 1;
            $user->register_date = time();
            $user->save();

            $userId = $user->id;
            echo "创建测试用户，ID: {$userId}\n";

            // 为该用户添加系统日志
            $syslog = new \App\Models\Syslogs();
            $syslog->userid = (string)$userId;
            $syslog->modulename = 'Test';
            $syslog->actionname = 'test_action';
            $syslog->opname = 'delete_validation_test';
            $syslog->message = '测试删除验证';
            $syslog->userip = '127.0.0.1';
            $syslog->create_time = time();
            $syslog->save();

            echo "为用户添加系统日志记录\n";

            // 尝试删除该用户（应该失败）
            $result = $user->delete();
            if ($result === false) {
                echo "✅ 删除验证成功拦截\n";
                $errors = $user->getErrors();
                foreach ($errors->all() as $error) {
                    echo "错误信息: {$error}\n";
                }

                // 清理测试数据
                $syslog->delete();
                $user->delete();
                echo "已清理测试数据（先删除日志，再删除用户）\n";
            } else {
                echo "❌ 删除成功（应该失败）\n";
                // 清理
                $syslog->delete();
            }
        } catch (\Exception $e) {
            echo "⚠️  异常: " . $e->getMessage() . "\n";
        }

        echo "\n";

        // 测试 3: 删除普通用户（应该成功）
        echo "测试 3: 删除普通用户（无系统日志）\n";
        echo "----------------------------------------\n";
        try {
            // 先创建一个测试用户
            $user = new User();
            $user->nick_name = 'delete_test_user';
            $user->pwd = '123456';
            $user->telephone = '13900139888';
            $user->email = 'delete_test@example.com';
            $user->status = 1;
            $user->register_date = time();
            $user->save();

            $userId = $user->id;
            echo "创建测试用户，ID: {$userId}\n";

            // 尝试删除（应该成功）
            $result = $user->delete();
            if ($result === false) {
                echo "❌ 删除失败: " . $user->getErrors()->first() . "\n";
            } else {
                echo "✅ 删除成功\n";
            }

        } catch (\Exception $e) {
            echo "⚠️  异常: " . $e->getMessage() . "\n";
        }

        echo "\n=== 测试完成 ===\n\n";
    }

    /**
     * 测试联动删除功能
     * 运行命令: php /path/to/tp.php /Home/Test/cascadeDelete
     */
    public function cascadeDelete(){
        echo "\n=== 测试联动删除功能 ===\n\n";

        // 测试 1: 删除用户时自动删除 role_user 关联
        echo "测试 1: 删除用户时自动删除 role_user 关联\n";
        echo "----------------------------------------\n";
        try {
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
            echo "创建测试用户，ID: {$userId}\n";

            // 为用户添加角色关联
            \Illuminate\Database\Capsule\Manager::table('role_user')->insert([
                'user_id' => $userId,
                'role_id' => 2,
            ]);

            $roleUserCount = \Illuminate\Database\Capsule\Manager::table('role_user')
                ->where('user_id', $userId)
                ->count();
            echo "为用户添加角色关联，关联记录数: {$roleUserCount}\n";

            // 删除用户（应该自动删除 role_user 关联）
            $result = $user->delete();

            if ($result === false) {
                echo "❌ 删除失败: " . $user->getErrors()->first() . "\n";
                // 清理
                \Illuminate\Database\Capsule\Manager::table('role_user')
                    ->where('user_id', $userId)
                    ->delete();
                $user->delete();
            } else {
                echo "✅ 用户删除成功\n";

                // 检查 role_user 是否也被删除
                $remainingCount = \Illuminate\Database\Capsule\Manager::table('role_user')
                    ->where('user_id', $userId)
                    ->count();

                if ($remainingCount === 0) {
                    echo "✅ 联动删除成功，role_user 记录已自动删除\n";
                } else {
                    echo "❌ 联动删除失败，仍有 {$remainingCount} 条 role_user 记录\n";
                    // 清理
                    \Illuminate\Database\Capsule\Manager::table('role_user')
                        ->where('user_id', $userId)
                        ->delete();
                }
            }

        } catch (\Exception $e) {
            echo "⚠️  异常: " . $e->getMessage() . "\n";
        }

        echo "\n=== 测试完成 ===\n\n";
    }
}
