<?php
/**
 * Fixture — 方式 C 的示例被测靶子。
 *
 * 这是一个「持有注入 DB 依赖」的 Service 类示例：构造函数接收一个 DB 对象，
 * 业务方法通过该依赖发起查询。实际项目中，你自己的 Repository/Service 类
 * （构造函数注入 DB 或 Capsule 依赖）可直接替换本类作为被测对象。
 *
 * 注意：本类仅用于演示 Kahlan 的注入 mock 写法，非项目真实业务代码。
 */

namespace Specs\Support\InjectionTarget;

class OrderService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * 按状态过滤订单并对金额求和。
     *
     * @param  array $cond 含 status 的条件（未传则默认 1）
     * @return mixed       求和结果（来自 DB 依赖）
     */
    public function summary(array $cond)
    {
        $status = $cond['status'] ?? 1;

        return $this->db->table('order')
                        ->where('status', $status)
                        ->sum('amount');
    }
}
