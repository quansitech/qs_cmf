<?php
/**
 * fixture —— 用于 DependencyInjectionSpec 的控制器。
 *
 * 设计要点：
 * - 不继承 QsController（避免触发 _initialize 的 RBAC/菜单副作用），独立验证 DI 机制。
 * - 构造函数声明 LoggerService 类型参数，验证 qs_instantiate() 的构造注入能力。
 * - 提供 action 方法（含类型注入 + 标量参数混合），验证 App::exec() 风格的方法注入。
 */
namespace Specs\Support\InjectionTarget;

use Specs\Support\InjectionTarget\LoggerService;

class LoggerController
{
    public LoggerService $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * action 示例：混合类型注入 + 标量参数。
     * App::exec() 改造后，LoggerService 走容器，$message 走请求参数绑定。
     */
    public function write(LoggerService $svc, string $message = 'default'): string
    {
        $svc->log($message);
        return $message;
    }

    public function count(): int
    {
        return count($this->logger->all());
    }
}
