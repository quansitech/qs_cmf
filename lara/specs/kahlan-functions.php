<?php
/**
 * Kahlan DSL 全局函数 shim。
 *
 * 背景：think-core 依赖 laravel/framework ^11，其 Foundation/helpers.php 在 Composer
 * autoload 阶段定义了全局 context() 函数，与 Kahlan 的 context() 冲突。而 Kahlan 的
 * initKahlanGlobalFunctions() 一旦检测到任一冲突即 exit(1)，导致无法运行。
 *
 * 解法：本 shim 在 Kahlan 加载 spec 前，手动注册除 context() 系列（context / fcontext /
 * xcontext，它们内部调用 context）外的全部 DSL 函数，并 define('KAHLAN_FUNCTIONS_EXIST')。
 * - Kahlan 的 initKahlanGlobalFunctions() 见 KAHLAN_FUNCTIONS_EXIST 已定义 → 直接 return；
 * - Kahlan\Cli\Kahlan::run() 同样据此判定函数已就绪 → 正常运行。
 *
 * 影响：spec 内不可使用 context()（已被 Laravel 占用），用 describe() 嵌套代替分组。
 *
 * 本文件在 Kahlan 配置加载阶段（loadConfig）被 require，早于 initKahlanGlobalFunctions()。
 */

use Kahlan\Suite;
use Kahlan\Allow;

if (defined('KAHLAN_FUNCTIONS_EXIST')) {
    return; // 已注册，避免重复
}
define('KAHLAN_FUNCTIONS_EXIST', true);

function beforeAll($closure)
{
    return Suite::current()->beforeAll($closure);
}

function afterAll($closure)
{
    return Suite::current()->afterAll($closure);
}

function beforeEach($closure)
{
    return Suite::current()->beforeEach($closure);
}

function afterEach($closure)
{
    return Suite::current()->afterEach($closure);
}

function describe($message, $closure, $timeout = null, $type = 'normal')
{
    if (!Suite::current()) {
        $suite = \Kahlan\box('kahlan')->get('suite.global');
        return $suite->root()->describe($message, $closure, $timeout, $type);
    }
    return Suite::current()->describe($message, $closure, $timeout, $type);
}

// 注：context() 不注册 —— 与 Laravel 11 helper 冲突。用 describe() 嵌套代替。

function given($name, $value)
{
    return Suite::current()->given($name, $value);
}

function it($message, $closure = null, $timeout = null, $type = 'normal')
{
    return Suite::current()->it($message, $closure, $timeout, $type);
}

function fdescribe($message, $closure, $timeout = null)
{
    return describe($message, $closure, $timeout, 'focus');
}

// 注：fcontext() / xcontext() 不注册 —— 依赖 context()。

function fit($message, $closure = null, $timeout = null)
{
    return it($message, $closure, $timeout, 'focus');
}

function xdescribe($message, $closure, $timeout = null)
{
    return describe($message, $closure, $timeout, 'exclude');
}

function xit($message, $closure = null, $timeout = null)
{
    return it($message, $closure, $timeout, 'exclude');
}

function waitsFor($actual, $timeout = 60)
{
    return Suite::current()->waitsFor($actual, $timeout);
}

function skipIf($condition)
{
    $current = Suite::current();
    $current->skipIf($condition);
}

function skipIfWindows()
{
    $current = Suite::current();
    $current->skipIf(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
}

function expect($actual)
{
    return Suite::current()->expect($actual);
}

function allow($actual)
{
    return new Allow($actual);
}
