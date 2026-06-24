<?php
/**
 * fixture —— 用于 DependencyInjectionSpec 的可注入服务。
 *
 * 演示控制器构造注入 + 容器替换的典型场景：一个无状态 service，被控制器持有。
 */
namespace Specs\Support\InjectionTarget;

class LoggerService
{
    private array $logs = [];

    public function log(string $message): void
    {
        $this->logs[] = $message;
    }

    public function all(): array
    {
        return $this->logs;
    }
}
