<?php
/**
 * Kahlan bootstrap — 纯单元测试环境引导。
 *
 * 设计原则：
 * - 不连业务 Postgres 库、不跑 migrate。
 * - 只加载 Composer autoload + think-core 的 .class.php 类加载器。
 * - 轻量 boot Capsule 连一个空内存 SQLite 库（让 Eloquent 能构建/执行查询）。
 * - 自动初始化验证框架（Validator Factory + Facade 容器），让测试模型增删改验证
 *   的 spec 无需关心 watson/validating 的 Event Facade 机制与全局变量。
 * - 通过 Connection::beforeExecuting 钩子捕获 SQL（不依赖 uopz/runkit 扩展）。
 *
 * 提供全局辅助函数：
 * - captureSql(callable): 执行一段逻辑，捕获期间触发的 SQL（含绑定值）。
 * - createTablesFor(array): 在内存库为给定模型建空表，使查询返回空结果而非报错。
 * - defineTables(array, bool): 声明式批量建表（含关联表），支持精确定义列。
 * - cleanTables(array): 批量清空表数据（用例隔离）。
 * - resetSqlBuffer(): 清空 SQL 捕获缓冲（每个 it 前调用）。
 * - loadCachedSchema(): 从迁移生成的缓存加载全部业务表结构（migrate 后自动刷新）。
 *
 * 说明：ThinkPHP 运行时常量（IS_POST / MODULE_NAME 等）与 sysLogs() 仅在控制器的
 * _initialize() 和 public action 里使用。测纯 DB 逻辑的 protected 方法时（用
 * bindControllerWithoutInit 跳过 _initialize）无需桩——如确需测 public action，
 * 在对应 spec 文件内自行 define 常量、定义 sysLogs no-op。
 */

define('QS_PROJECT_ROOT', dirname(__DIR__, 2));

// 1. Composer 自动加载（include_files 顺带载入 think-core 函数与 Eloquent macros）
require QS_PROJECT_ROOT . '/vendor/autoload.php';

// 1b. 预定义 ThinkPHP 运行时常量与 URL 配置 —— 这些在生产环境由 ThinkPHP 的
//     Dispatcher/convention 阶段定义/加载，但 Kahlan 单元环境不走 ThinkPHP 请求流程，
//     需在此预置，否则 U() 等函数会因常量未定义（__APP__/__ROOT__）或配置为 null
//     （URL_PATHINFO_DEPR 触发 str_replace deprecation）而报错。
if (!defined('__APP__')) {
    define('__APP__', '/index.php');
}
if (function_exists('C') && !C('URL_PATHINFO_DEPR')) {
    C('URL_PATHINFO_DEPR', '/');
}

// 2. 注册 think-core / app 下 ThinkPHP 风格 .class.php 类的自动加载器。
//    复刻 Testing\TestCase::loadTpConfig() 的加载逻辑，但不依赖 Laravel 的 base_path()。
spl_autoload_register(function ($class) {
    $name    = strstr($class, '\\', true);
    $libPath = QS_PROJECT_ROOT . '/vendor/tiderjian/think-core/src/Library/';

    // 命名空间首段若存在于 Library 目录下，则定位到 Library；否则定位到 app/。
    if (is_dir($libPath . $name)) {
        $path = $libPath;
    } else {
        $path = QS_PROJECT_ROOT . '/app/';
    }

    $filename = $path . str_replace('\\', '/', $class) . '.class.php';
    if (is_file($filename)) {
        require $filename;
    }
});

// 2b. spec 辅助类（fixture）命名空间：Specs\Support\ → lara/specs/Support/
spl_autoload_register(function ($class) {
    $prefix = 'Specs\\Support\\';
    if (str_starts_with($class, $prefix)) {
        $relative = substr($class, strlen($prefix));
        $file = __DIR__ . '/Support/' . str_replace('\\', '/', $relative) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

// 3. 轻量 boot Capsule —— 仅连内存 SQLite，让 Eloquent 能正常构建查询。
//    不涉及业务库、不建表、不 migrate。
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

$capsule = new Capsule();
$memoryConfig = [
    'driver'   => 'sqlite',
    'database' => ':memory:',
    'prefix'   => '',
];
$capsule->addConnection($memoryConfig); // 默认连接
$capsule->setEventDispatcher(new Dispatcher(new Container()));
$capsule->setAsGlobal();
$capsule->bootEloquent();

// 3b. 框架胶水自动初始化 —— 让 spec 作者无需了解 watson/validating 的 Event Facade 机制、
//     $GLOBALS['laravel_validator_factory'] 全局即可测试模型增删改验证。复刻生产环境
//     Behavior\EloquentLoadBehavior 的最小版本（ArrayLoader 不依赖 lang 翻译文件）。
//     幂等：bootstrap 只在 Kahlan 启动时跑一次，守卫是防御性的。
if (!isset($GLOBALS['laravel_validator_factory'])) {
    $container = new Container();
    // 复用 bootEloquent 注册的事件调度器（saving/deleting 事件经 Event Facade 触发验证）
    $container->instance('events', \Illuminate\Database\Eloquent\Model::getEventDispatcher());
    \Illuminate\Support\Facades\Facade::setFacadeApplication($container);

    $translator = new \Illuminate\Translation\Translator(new \Illuminate\Translation\ArrayLoader(), 'zh_CN');
    $GLOBALS['laravel_validator_factory'] = new \Illuminate\Validation\Factory($translator);
}

// 3c. DI 容器单例初始化 —— 让 app() 在 Kahlan 下可用，使控制器可走依赖注入。
//     复用 3b 已建的事件调度器，避免重复创建；若 3b 未建容器（$GLOBALS 守卫跳过），
//     此处兜底建一个。注册 RBAC 等核心接口默认绑定，spec 可随时覆盖。
$diContainer = Container::getInstance();
if ($diContainer === null) {
    $diContainer = new Container();
    Container::setInstance($diContainer);
}
// 事件调度器：若 Eloquent 已注册则复用，否则留空（spec 按需设置）
if (! $diContainer->bound('events')) {
    $events = \Illuminate\Database\Eloquent\Model::getEventDispatcher();
    if ($events !== null) {
        $diContainer->instance('events', $events);
    }
}
\Illuminate\Support\Facades\Facade::setFacadeApplication($diContainer);

// 注册 RBAC 决策器默认绑定（默认放行，符合「单测不应触发权限校验」的惯例）。
// 默认实现 RbacChecker 会调真实 QsRbac，但单测常无 session/db，故提供一个放行的默认替身。
// 需要真实权限校验的 spec 可覆盖：app()->instance(RbacCheckerInterface::class, new RbacChecker());
if (! $diContainer->bound(\Qscmf\Contracts\RbacCheckerInterface::class)) {
    $diContainer->instance(
        \Qscmf\Contracts\RbacCheckerInterface::class,
        new class implements \Qscmf\Contracts\RbacCheckerInterface {
            public function accessDecision(): bool { return true; }
            public function checkAccessNodeId($authId, int $nodeId): bool { return true; }
        }
    );
}

// QsController 构造注入的默认协作者：绑成无副作用 mock，spec 内即可零参 new Controller()。
// QsController::__construct(?AuthStarter, ?BackendInitializer) 在参数为 null 时走容器兜底，
// 故此处绑定让所有控制器子类的「默认构造」天然跳过 _initialize 的鉴权/菜单/Hook 副作用。
// 注：这些是 Qscmf\Core 下的 think-core 协作者，其结构契约测试应在 think-core 仓库完成；
// qs_cmf 的 specs 绑定 mock 仅服务于自身控制器测试，不承担 core 协作者的测试职责。
if (! $diContainer->bound(\Qscmf\Core\AuthStarter::class)) {
    $diContainer->instance(\Qscmf\Core\AuthStarter::class, mockAuthStarter());
}
if (! $diContainer->bound(\Qscmf\Core\BackendInitializer::class)) {
    $diContainer->instance(\Qscmf\Core\BackendInitializer::class, mockBackendInitializer());
}

// 4. SQL 捕获：通过 beforeExecuting 钩子记录所有即将执行的 SQL（执行前触发，不受查询成败影响）。
$GLOBALS['__qs_kahlan_sql_buffer'] = [];

Capsule::connection()->beforeExecuting(function ($query, $bindings, $conn) {
    $GLOBALS['__qs_kahlan_sql_buffer'][] = ['sql' => $query, 'bindings' => $bindings];
});

// 4b. 按缓存加载全部业务表结构 —— 替代 spec 内手写 defineTables([...])。
//     缓存由 App\Support\SchemaCacheBuilder 在 php artisan migrate 后自动生成
//     （KahlanSchemaCacheServiceProvider 监听 MigrationsEnded 触发）。
//     加载后，spec 的 beforeAll 通常无需再建表，beforeEach 只需 cleanTables() 隔离数据。
//     缓存缺失（未跑 migrate）时静默跳过，不影响现有 spec 的 defineTables。
loadCachedSchema();

/**
 * 执行一段逻辑，捕获期间触发的所有 SQL 语句。
 *
 * 查询会真正发往内存 SQLite 库——若相关表未建，查询会抛异常。
 * 如需被测逻辑跑完整流程（而非中途因表不存在抛错），请先用 createTablesFor() 建表。
 *
 * 用法：
 *   $sqls = captureSql(function () {
 *       \App\Models\User::getUserByEmailOrNickName('test@example.com');
 *   });
 *   expect($sqls[0]['sql'])->toContain('select * from');
 *   expect($sqls[0]['bindings'])->toBe(['test@example.com']);
 *
 * @param  callable $fn 要执行的逻辑
 * @return array        元素为 ['sql' => string, 'bindings' => array]
 */
function captureSql(callable $fn): array
{
    $GLOBALS['__qs_kahlan_sql_buffer'] = [];
    $fn();
    return $GLOBALS['__qs_kahlan_sql_buffer'];
}

/**
 * 清空 SQL 捕获缓冲（通常在每个 it/example 前调用）。
 */
function resetSqlBuffer(): void
{
    $GLOBALS['__qs_kahlan_sql_buffer'] = [];
}

/**
 * 在内存库为给定模型创建空表，使查询返回空结果而非「no such table」异常。
 *
 * 仅按模型属性粗略建表（id + 一个 text 列），用于让被测逻辑跑完整流程。
 * 若被测逻辑依赖具体字段/类型，可在 spec 内自行 Capsule::schema()->table(...) 调整。
 *
 * 用法：
 *   createTablesFor([\App\Models\User::class]);
 *
 * @param  array $modelClasses 模型类全限定名数组
 */
function createTablesFor(array $modelClasses): void
{
    foreach ($modelClasses as $class) {
        $table = (new \ReflectionClass($class))->newInstanceWithoutConstructor()->getTable();
        if (!Capsule::schema()->hasTable($table)) {
            Capsule::schema()->create($table, function ($blueprint) {
                $blueprint->id();
                $blueprint->text('__placeholder')->nullable();
            });
        }
    }
}

/**
 * 声明式批量建表（含关联表），用于需要真实列做插入/更新/计数断言的场景。
 *
 * 与 createTablesFor() 的区别：createTablesFor 只建 id + 占位列（仅让查询不报「no such table」），
 * 本函数由调用者用闭包精确定义每张表的列，适合写入数据并断言的测试。
 *
 * 幂等：已存在的表默认跳过；$dropFirst = true 时先 drop 再 create（重建 schema）。
 *
 * 用法：
 *   defineTables([
 *       'spec_article' => function ($t) {
 *           $t->id();
 *           $t->string('title')->nullable();
 *           $t->tinyInteger('status')->default(1);
 *       },
 *       'spec_syslog' => function ($t) {
 *           $t->id();
 *           $t->integer('userid');
 *       },
 *   ]);
 *
 * @param  array    $tables     表名 => 闭包(Blueprint $t) 的映射
 * @param  bool     $dropFirst  是否先 drop 再 create（默认 false，已存在则跳过）
 */
function defineTables(array $tables, bool $dropFirst = false): void
{
    foreach ($tables as $name => $callback) {
        if ($dropFirst && Capsule::schema()->hasTable($name)) {
            Capsule::schema()->drop($name);
        }
        if (!Capsule::schema()->hasTable($name)) {
            Capsule::schema()->create($name, $callback);
        }
    }
}

/**
 * 从迁移生成的 schema 缓存加载全部业务表结构。
 *
 * 缓存文件由 App\Support\SchemaCacheBuilder 重放 migration 建表逻辑生成（仅 DDL，
 * 无数据），经 KahlanSchemaCacheServiceProvider 在 php artisan migrate 后自动刷新。
 *
 * 与 defineTables() 的区别：
 * - defineTables：spec 作者手写列定义，精确但繁琐，且 schema 一变两边都要改；
 * - loadCachedSchema：直接复用迁移的权威 schema，spec 无需关心列定义。
 *
 * 幂等：已存在的表跳过（spec 仍可用 defineTables 补建 fixture 表或覆盖业务表）。
 * 先建 table 再建 index（索引依赖表存在）。
 *
 * 缓存缺失（未跑 migrate，或 production 环境）时静默跳过，不影响现有 spec 的
 * defineTables/createTablesFor。错误不抛出——缓存损坏不应阻塞测试启动，spec 若依赖
 * 具体表会在用到时报错，更易定位。
 */
function loadCachedSchema(): void
{
    $cacheFile = __DIR__ . '/Support/schema_cache.php';
    if (!is_file($cacheFile)) {
        return;
    }

    $schema = require $cacheFile;
    if (!is_array($schema)) {
        return;
    }

    $conn = Capsule::connection();

    // 先建表，再建索引（索引依赖所属表已存在）。
    foreach ($schema as $name => $item) {
        if (($item['type'] ?? null) === 'table' && !Capsule::schema()->hasTable($name)) {
            $conn->statement($item['sql']);
        }
    }
    foreach ($schema as $item) {
        if (($item['type'] ?? null) === 'index') {
            $conn->statement($item['sql']);
        }
    }
}

/**
 * 批量清空表数据（用例隔离），替代手写 foreach + Capsule::table()->delete()。
 *
 * 注意：本函数只清数据、不动 schema；如需同时清 SQL 捕获缓冲，请配套调用 resetSqlBuffer()。
 *
 * 用法（配合 beforeEach）：
 *   beforeEach(function () {
 *       cleanTables(['spec_article', 'spec_syslog']);
 *       resetSqlBuffer();
 *   });
 *
 * @param  array $tables 表名数组
 */
function cleanTables(array $tables): void
{
    foreach ($tables as $table) {
        Capsule::table($table)->delete();
    }
}

// ============================================================================
// 控制器无副作用实例化（swapClass 的正规替代品）
// ============================================================================
//
// 历史背景：旧方案用 swapClass() 在 autoload 阶段把 QsController 整体 class_alias
// 成 FakeQsController，从而让 new UserController() 跑空构造、不触发 _initialize。
// 这套「autoload 劫持」全局污染类符号表，且依赖加载时序，是脆弱的黑魔法。
//
// 新方案：用 ReflectionClass::newInstanceWithoutConstructor() 在实例层绕过构造，
// 既跳过 _initialize（RBAC/菜单/session/Hook 副作用），又不改动任何类定义。
// 配合容器 bind()，让 qs_instantiate() / app()->make() 也能返回无副作用实例。
//
// PHP 语言机制保证：newInstanceWithoutConstructor 不调用 __construct，因此
// Think\Controller::__construct() 里的 method_exists($this,'_initialize') 检查
// 根本不会执行 —— 比 swapClass 的「让 method_exists 返回 false」更彻底。
//
// 另一条路径（推荐）：QsController 的 _initialize 已重构为委托 BackendInitializer
// 协作者（构造注入）。测试时传入 mock 的 AuthStarter + BackendInitializer，控制器走
// 真实构造但 _initialize 的副作用被 mock 吞掉。见 mockAuthStarter / mockBackendInitializer。

/**
 * 返回一个鉴权协作者的 mock：resetRbac/verifyLogin/authorize 全为 no-op。
 *
 * 用于 QsController 构造注入（__construct(?AuthStarter, ?BackendInitializer)），
 * 测试时传入此 mock，让 _initialize 里的鉴权三步不产生副作用。
 *
 * @return \Qscmf\Core\AuthStarter
 */
function mockAuthStarter(): \Qscmf\Core\AuthStarter
{
    return new class extends \Qscmf\Core\AuthStarter {
        public function resetRbac(): void {}
        public function verifyLogin(): void {}
        public function authorize(): void {}
    };
}

/**
 * 返回一个后台初始化协作者的 mock：initialize() 全为 no-op。
 *
 * 用于 QsController 构造注入，测试时传入此 mock，让 _initialize 委托的菜单/Hook/
 * layoutProps 全部副作用被吞掉。这是消除 _initialize 副作用的最干净方式——
 * mock 整个协作者 = 跳过全部后台初始化逻辑。
 *
 * @return \Qscmf\Core\BackendInitializer
 */
function mockBackendInitializer(): \Qscmf\Core\BackendInitializer
{
    return new class extends \Qscmf\Core\BackendInitializer {
        public function __construct()
        {
            // 不调父构造（父构造需要 RbacCheckerInterface，测试 mock 无需）
        }

        public function initialize(\Qscmf\Core\QsController $controller): void
        {
            // no-op：吞掉全部后台初始化副作用
        }
    };
}



/**
 * 创建一个跳过构造函数（含 _initialize）的控制器实例。
 *
 * 用 ReflectionClass::newInstanceWithoutConstructor() 绕过 __construct，从而：
 * - 不触发 QsController::_initialize() 的 RBAC / 菜单 / session / Hook 副作用
 * - 不实例化 View、不调 Hook::listen('action_begin')
 *
 * 实例的字段保持未初始化状态（除类属性默认值），被测方法若依赖这些字段，需在 spec 里
 * 手动赋值（或用 bindControllerWithoutInit 后走 qs_instantiate 拿到同样效果的实例）。
 *
 * 这是测控制器 protected/public 方法时的标准构造方式，替代已删除的 swapClass。
 *
 * @param  string $class 控制器类全限定名
 * @return object
 */
function makeControllerWithoutInit(string $class): object
{
    return (new \ReflectionClass($class))->newInstanceWithoutConstructor();
}

/**
 * 在容器绑定一个「无副作用构造」的工厂，使 qs_instantiate($class) / app()->make($class)
 * 返回跳过 _initialize 的实例。
 *
 * 与 makeControllerWithoutInit() 的区别：本函数注册到容器，后续所有走容器的实例化
 * （含 think-core 的 controller() 函数）都会拿到无副作用实例；适合在 describe/beforeAll
 * 里一次性设置，影响整个 spec 文件。
 *
 * 注意：直接 `new $class()` 仍会走真实构造（PHP 语言行为，容器拦不住直接 new），
 * 因此 spec 内必须用 qs_instantiate() / app()->make() 获取实例。
 *
 * 用法：
 *   beforeAll(function () {
 *       bindControllerWithoutInit(\Admin\Controller\UserController::class);
 *   });
 *   // 之后用 qs_instantiate(UserController::class) 拿到无副作用实例
 *
 * @param string $class 控制器类全限定名
 */
function bindControllerWithoutInit(string $class): void
{
    app()->bind($class, function () use ($class) {
        return makeControllerWithoutInit($class);
    });
}

/**
 * 调用对象的 protected/private 方法，替代每个 spec 内重复定义反射闭包。
 *
 * PHP 8.1+ 起，ReflectionMethod::invokeArgs() 调用非 public 方法不再需要
 * setAccessible(true)（实测对 private 同样有效），因此本函数无需 setAccessible。
 *
 * 用法：
 *   $result = callProtected($controller, 'applySearchConditions', [$query, $data]);
 *   $result = callProtected($controller, 'getRoleOptions');
 *
 * @param  object $obj    被测对象
 * @param  string $method 方法名（protected 或 private 均可）
 * @param  array  $args   位置参数数组
 * @return mixed
 */
function callProtected(object $obj, string $method, array $args = [])
{
    return (new \ReflectionMethod($obj, $method))->invokeArgs($obj, $args);
}

// ============================================================================
// 容器绑定辅助函数
// ============================================================================

/**
 * 把一个类绑定到容器（替身实例或工厂闭包），供 app()->make() 解析时返回替身。
 *
 * 在容器层面替换被测类的依赖，不污染 PHP 类符号表。当被测类通过 qs_instantiate() /
 * app()->make() 实例化时（controller() 函数已改造为走容器），绑定的替身会被返回。
 *
 * 用法：
 *   // 绑定替身实例（Double）
 *   $rbac = Double::instance(['implements' => [RbacCheckerInterface::class]]);
 *   allow($rbac)->toReceive('accessDecision')->andReturn(false);
 *   bindStub(RbacCheckerInterface::class, $rbac);
 *
 *   // 绑定工厂闭包
 *   bindStub(Menu::class, fn() => Double::instance());
 *
 * @param string            $abstract 类名或接口名
 * @param object|\Closure   $concrete 替身实例，或返回实例的闭包
 */
function bindStub(string $abstract, $concrete): void
{
    app()->instance($abstract, $concrete);
}


/**
 * 解除容器绑定（恢复默认行为）。
 *
 * 注意：Illuminate\Container 的 instance() 绑定无法真正「删除」，本函数通过重新
 * 绑定一个走默认解析的闭包来近似实现。对于单测的 afterEach 清理，更推荐在 beforeEach
 * 里用 bindStub 设置，而非依赖 unbind —— 因为 Kahlan 每个文件是独立进程，无跨文件污染。
 *
 * @param string $abstract 类名或接口名
 */
function unbindStub(string $abstract): void
{
    $container = app();
    if ($container->bound($abstract)) {
        $container->bind($abstract, null);
    }
}

