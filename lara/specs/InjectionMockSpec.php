<?php
/**
 * 示例 spec — 演示方式 C：被测代码持有注入的 DB 对象，用 Kahlan Double 伪造它。
 *
 * 方式 C 适用场景：被测类通过构造函数/属性持有一个 DB 依赖（如 $this->db），
 * 所有查询经该依赖发出。这种情况下无需 boot Capsule，直接用 Double 伪造依赖即可，
 * 是最纯粹的单元测试。
 *
 * Kahlan 的 mock 惯用法（与 Jasmine 一致）：
 *   1. 先 expect($obj)->toReceive('method')->with(...) 声明调用期望；
 *   2. 再执行被测代码；
 *   3. Kahlan 在 spec 结束时自动校验所有期望是否被满足。
 *
 * 本 spec 用 fixture 类 OrderService 作为被测靶子（项目暂无典型 Repository 类，
 * 实际使用时替换成你自己的类即可）。fixture 定义在 Support/InjectionTarget/OrderService.php。
 */

use Kahlan\Plugin\Double;

describe('方式 C：mock 注入的 DB 对象', function () {

    describe('OrderService::summary', function () {

        it('按传入状态过滤并对 amount 求和', function () {
            // 用 Double 伪造注入的 DB 依赖，无需真实数据库
            $db = Double::instance();

            // 1) 先声明调用期望（链式调用按声明顺序匹配）
            expect($db)->toReceive('table')->with('order');
            expect($db)->toReceive('where')->with('status', 2);
            expect($db)->toReceive('sum')->with('amount');
            // 设置链式返回（table/where 返回自身，sum 返回求和值）
            allow($db)->toReceive('table')->andReturn($db);
            allow($db)->toReceive('where')->andReturn($db);
            allow($db)->toReceive('sum')->andReturn(1500);

            // 2) 执行被测代码
            $service = new \Specs\Support\InjectionTarget\OrderService($db);
            $result = $service->summary(['status' => 2]);

            // 3) 断言返回值（调用期望由 Kahlan 在 spec 结束时自动校验）
            expect($result)->toBe(1500);
        });

        it('未传 status 时默认按 status=1 查询', function () {
            $db = Double::instance();

            expect($db)->toReceive('where')->with('status', 1);
            allow($db)->toReceive('table')->andReturn($db);
            allow($db)->toReceive('where')->andReturn($db);
            allow($db)->toReceive('sum')->andReturn(0);

            $service = new \Specs\Support\InjectionTarget\OrderService($db);
            $service->summary([]); // 未传 status，期望默认 1
        });
    });
});
