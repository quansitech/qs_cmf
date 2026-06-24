<?php
/**
 * spec —— 验证 think-core 的 Laravel 式依赖注入改造。
 *
 * 覆盖三个核心能力：
 * 1. 容器基建：app() / qs_container() / qs_instantiate() 可用。
 * 2. 构造注入：qs_instantiate() 解析类时，构造函数的类型依赖被自动注入。
 * 3. mock 替换：通过 bindStub() / app()->instance() 替换容器绑定，
 *    被 qs_instantiate() 拿到的实例即为替身 —— 这是「跑测试时 mock 掉依赖」的关键。
 *
 * 另含一节验证 QsController 的 RBAC 决策器可通过容器替换（接口注入的实战）。
 */

use Kahlan\Plugin\Double;
use Qscmf\Contracts\RbacCheckerInterface;
use Specs\Support\InjectionTarget\LoggerController;
use Specs\Support\InjectionTarget\LoggerService;

describe('think-core 依赖注入改造', function () {

    describe('容器基建', function () {

        it('app() 返回容器单例', function () {
            expect(app())->toBeAnInstanceOf(\Illuminate\Container\Container::class);
        });

        it('qs_container() 与 app() 返回同一实例', function () {
            expect(qs_container())->toBe(app());
        });

        it('容器单例幂等（多次 app() 调用返回同一对象）', function () {
            expect(app())->toBe(app());
        });
    });

    describe('qs_instantiate 构造注入', function () {

        it('构造函数的类型依赖被自动注入', function () {
            $ctrl = qs_instantiate(LoggerController::class);

            expect($ctrl)->toBeAnInstanceOf(LoggerController::class);
            expect($ctrl->logger)->toBeAnInstanceOf(LoggerService::class);
        });

        it('app()->make() 与 qs_instantiate 行为一致', function () {
            $a = qs_instantiate(LoggerController::class);
            $b = app()->make(LoggerController::class);

            expect($b)->toBeAnInstanceOf(LoggerController::class);
            expect($b->logger)->toBeAnInstanceOf(LoggerService::class);
        });
    });

    describe('容器替换（mock 能力）', function () {

        afterEach(function () {
            // 清理：恢复默认绑定，避免影响后续 spec
            app()->forgetInstance(LoggerService::class);
            app()->forgetInstance(LoggerController::class);
        });

        it('bindStub 注入替身实例后，qs_instantiate 拿到的是替身', function () {
            $mock = Double::instance(['extends' => LoggerService::class]);
            allow($mock)->toReceive('log')->andReturn(null);

            bindStub(LoggerService::class, $mock);

            $ctrl = qs_instantiate(LoggerController::class);
            expect($ctrl->logger)->toBe($mock);
        });

        it('app()->instance 注入替身后，构造注入的依赖被替换', function () {
            $spy = new LoggerService();
            $spy->log('injected-via-instance');

            app()->instance(LoggerService::class, $spy);

            $ctrl = qs_instantiate(LoggerController::class);
            expect($ctrl->logger)->toBe($spy);
            expect($ctrl->logger->all())->toBe(['injected-via-instance']);
        });

        it('method action 的类型参数经容器解析，可被 mock 替换', function () {
            // 模拟 App::exec() 方法注入的解析逻辑：反射 action 参数，类型参数走容器
            $ctrl = qs_instantiate(LoggerController::class);
            $method = new ReflectionMethod($ctrl, 'write');

            $mockSvc = Double::instance(['extends' => LoggerService::class]);
            app()->instance(LoggerService::class, $mockSvc);

            $args = [];
            foreach ($method->getParameters() as $param) {
                $type = $param->getType();
                if ($type !== null && !$type->isBuiltin()) {
                    // 关键断言：容器解析出的就是 mock 替身，而非新建实例
                    $resolved = app()->make($type->getName());
                    expect($resolved)->toBe($mockSvc);
                    $args[] = $resolved;
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                }
            }

            expect($args[0])->toBe($mockSvc);
            expect($args[1])->toBe('default');
        });
    });

    describe('QsController RBAC 决策器可 mock', function () {

        afterEach(function () {
            // 恢复 bootstrap 注册的默认放行替身
            app()->forgetInstance(RbacCheckerInterface::class);
        });

        it('RbacCheckerInterface 已在容器绑定默认实现（放行）', function () {
            $rbac = app()->make(RbacCheckerInterface::class);

            expect($rbac)->toBeAnInstanceOf(RbacCheckerInterface::class);
            expect($rbac->accessDecision())->toBe(true);
            expect($rbac->checkAccessNodeId(1, 100))->toBe(true);
        });

        it('bindStub 替换 RBAC 决策器为 mock（模拟权限拒绝）', function () {
            $denyRbac = Double::instance(['implements' => [RbacCheckerInterface::class]]);
            allow($denyRbac)->toReceive('accessDecision')->andReturn(false);
            allow($denyRbac)->toReceive('checkAccessNodeId')->andReturn(false);

            bindStub(RbacCheckerInterface::class, $denyRbac);

            $resolved = app()->make(RbacCheckerInterface::class);
            expect($resolved)->toBe($denyRbac);
            expect($resolved->accessDecision())->toBe(false);
            expect($resolved->checkAccessNodeId(1, 1))->toBe(false);
        });
    });
});
