<!-- Parent: AGENTS.md -->
<!-- Generated: 2026-06-19 -->

# Kahlan 单元测试

## 这是什么

qs_cmf 的纯单元测试方案，基于 [Kahlan](https://kahlan.gitbooks.io/docs/)（一个 PHP BDD 测试框架）。它在内存 SQLite 上跑真实查询、或用 mock 模拟依赖，**毫秒级完成**，不连业务数据库、不跑 migrate。

> 本文档面向第一次在本项目写单元测试的开发者。如果你要写的是 HTTP 请求 / DB 迁移刷新 / 完整业务流程的测试，请改用 PHPUnit，见 [Testing.md](./Testing.md)。

## 两种测试体系对照

| 维度 | Kahlan（本文档） | PHPUnit（[Testing.md](./Testing.md)） |
|------|------------------|------------------------|
| 目录 | `lara/specs/` | `lara/tests/` |
| 用途 | 纯单元：SQL 断言、逻辑分支、mock 依赖 | 集成/功能：HTTP、DB 迁移刷新、完整流程 |
| 数据库 | 内存 SQLite（空库，按需建表） | Postgres 测试库，每用例 `migrate:refresh` |
| 速度 | 毫秒级 | 秒级 |
| 写法 | BDD（`describe` / `it` / `expect`） | `test方法` / `assertXxx` |

**判断标准**：需要真实业务数据库/HTTP/完整流程 → PHPUnit；只需断言生成的 SQL 或纯逻辑 → Kahlan。

## 运行命令

| 命令 | 说明 |
|------|------|
| `composer test:kahlan` | 跑全部 Kahlan 用例（`lara/specs/**/*.php`） |
| `composer test:kahlan:coverage` | 带覆盖率（需 Xdebug） |
| `vendor/bin/kahlan --config=kahlan-config.php lara/specs/FooSpec.php` | 只跑单个 spec 文件 |

> ⚠️ 改了 `bootstrap.php` / `kahlan-functions.php` 之后，加 `--cc` 清缓存，否则 Kahlan 会用旧的编译结果：
> `vendor/bin/kahlan --config=kahlan-config.php --cc`

## 核心概念：真实库 vs Mock，到底测的是什么

这是最容易混淆的一点，先讲清楚。

qs_cmf 的 Kahlan 测试里，**「数据库行为」要么走真实内存库，要么完全 mock**，不存在「半真半假」。两种模式下，你能信任的断言对象完全不同：

### 模式 A / B：真实内存 SQLite

```
你的代码 → 真实的 Eloquent / Capsule → 真实的 SQLite 驱动 → 内存库
```

**走真实的数据库行为**：save 真的会 INSERT、delete 真的会 DELETE、事务、外键约束、列类型都生效。你能断言：
- 模型的 `$model->exists`（save 成功才为 true）
- 库里的行数（`Capsule::table('x')->count()`）
- 实际发给驱动的 SQL（通过 `captureSql`）

> 💡 这就是为什么 `ModelRulesSpec` 里能放心用 `$ent->exists` 断言——没有 mock，Eloquent 在真实 save 成败后自己设置它，骗不了人。配套还用了 `count()` 和 `captureSql` 从数据库侧兜底。

### 模式 C：纯 Mock（不碰数据库）

```
你的代码 → 一个伪造的依赖对象（Double）→ 什么都不真正执行
```

**完全 mock**：用 `Kahlan\Plugin\Double` 造一个假对象替代真实依赖。被测代码调它的方法时不会真的查库，而是按你 `allow()->andReturn()` 设定的值返回。你能断言：
- 被测代码**调用了依赖的哪些方法、传了什么参数**（`expect($dep)->toReceive(...)`）
- 被测代码的**返回值/逻辑分支**

> ⚠️ 模式 C 下，**不要**断言 `$model->exists` 或 `count()`——因为根本没碰数据库，这些值没有意义。只断言「调用契约」和「返回值」。

### 怎么选

| 测的是 | 选哪个 |
|--------|--------|
| 模型方法最终生成什么 SQL（查询条件、绑定值、order by） | A（真实库 + captureSql） |
| 增删改验证（$rules / $deleteRules / $deleteCascade 等真实联动） | A（真实库） |
| 查询构造器链式调用生成的 SQL | B（真实库 + captureSql，用 `Capsule::table()`） |
| 一个接收依赖注入的 Service 类的逻辑（它怎么调 DB、默认值对不对） | C（mock 依赖） |

## bootstrap 自动提供了什么（spec 作者无需关心）

`lara/specs/bootstrap.php` 在测试启动时**一次性**做了这些事，spec 里不用重复写：

1. **Boot 一个空的内存 SQLite**（`:memory:`），通过 Capsule + Eloquent。
2. **注册 SQL 捕获钩子**：每条即将执行的 SQL 都会记进全局缓冲。
3. **初始化验证框架**（已下沉，对作者透明）：
   - Validator Factory 挂到 `$GLOBALS['laravel_validator_factory']`（`BaseModel::getValidator()` 取它）
   - Event Facade 容器绑定，让 watson/validating 的 `saving` 事件验证能触发
   - → 因此**测模型增删改验证时，你只需声明表 + 写断言，不用碰任何框架内部**
4. **类加载器**：
   - think-core 的 `.class.php` 类（如 `Qscmf\Core\BaseModel`）
   - `Specs\Support\*` 命名空间 → `lara/specs/Support/`（放你的 fixture）

## 提供的辅助函数

以下全局函数在 spec 里直接调用：

| 函数 | 说明 |
|------|------|
| `captureSql(callable $fn): array` | 执行一段逻辑，返回期间触发的 SQL。每条形如 `['sql' => string, 'bindings' => array]`。sql 带 `?` 占位符，绑定值在 bindings |
| `createTablesFor(array $modelClasses): void` | 为给定模型建**空表**（只有 id + 占位列）。仅适合只读空表场景 |
| `defineTables(array $tables, bool $dropFirst = false): void` | **声明式批量建表**（含关联表），`['表名' => 闭包($t)]`，精确定义列。幂等。适合需要写入/更新/计数的场景 |
| `cleanTables(array $tables): void` | 批量清空表数据（用例隔离）。只清数据不动 schema |
| `resetSqlBuffer(): void` | 清空 SQL 捕获缓冲（每个 `it` 前调用） |
| `stubTpRuntime(array $opts = []): void` | 按需桩 ThinkPHP 运行时常量（IS_POST/IS_AJAX/MODULE_NAME 等）+ sysLogs（no-op）。测控制器/业务逻辑时在 `beforeAll` 调用。I/C/U 不桩（think-core 已提供） |

> 💡 `createTablesFor` vs `defineTables`：前者建的表只有 `id` 和一个占位列，让「查空表」不报 `no such table` 就够了；一旦你要插入数据、`->sum('amount')`、按字段过滤，就必须用 `defineTables` 精确建列，否则 SQLite 报列缺失。

## 写一个 spec：从零开始

### 文件约定

- 文件放 `lara/specs/`，命名 `XxxSpec.php`（必须以 `Spec.php` 结尾）。
- spec 文件**不写 namespace**。
- **不要用 `context()`**（被 Laravel 11 的 helper 占用了，会冲突）。用嵌套 `describe()` 分组代替。

### 最小骨架

```php
<?php
use Illuminate\Database\Capsule\Manager as Capsule;

describe('我的测试组', function () {

    beforeAll(function () {
        // 一次性建表（幂等）
        defineTables([
            'spec_my_table' => fn($t) => [$t->id(), $t->string('name')],
        ]);
    });

    beforeEach(function () {
        cleanTables(['spec_my_table']);  // 每个用例前清空
        resetSqlBuffer();                // 清 SQL 缓冲
    });

    it('应该 ...', function () {
        // 被测代码 + 断言
        expect(true)->toBe(true);
    });
});
```

### DSL 速查

| DSL | 作用 |
|-----|------|
| `describe('标题', fn)` | 定义一组测试（可嵌套分组） |
| `it('应该...', fn)` | 一个测试用例 |
| `expect($val)->toBe($x)` | 断言相等 |
| `expect($val)->toContain('x')` | 断言包含子串/元素 |
| `expect($val)->toHaveLength(n)` | 断言长度 |
| `expect($val)->not->toBe($x)` | 否定断言 |
| `beforeAll(fn)` / `afterAll(fn)` | 整组前后各跑一次 |
| `beforeEach(fn)` / `afterEach(fn)` | 每个用例前后各跑一次 |

> 更多 matcher 见 Kahlan 官方文档。本项目用的是 `expect` / `allow` / `Double`。

## 完整示例

下面三个示例覆盖了三种典型场景，都可以直接参考。

### 示例 1（模式 A）：断言模型方法生成的 SQL

场景：测 `User::getUserByEmailOrNickName()` 到底按哪个字段查。用真实库 + `captureSql`。

```php
<?php
use App\Models\User;

describe('User 查询方法', function () {

    beforeAll(function () {
        createTablesFor([User::class]);  // 只需空表，让查询不报错
    });

    beforeEach(function () {
        resetSqlBuffer();
    });

    it('传入邮箱时按 email 字段查询', function () {
        $sqls = captureSql(function () {
            (new User())->getUserByEmailOrNickName('test@example.com');
        });

        expect($sqls)->toHaveLength(1);
        expect($sqls[0]['sql'])->toContain('select * from "user"');
        expect($sqls[0]['sql'])->toContain('"email" = ?');
        expect($sqls[0]['bindings'])->toBe(['test@example.com']);
    });

    it('传入非邮箱字符串时按 nick_name 字段查询', function () {
        $sqls = captureSql(function () {
            (new User())->getUserByEmailOrNickName('tider');
        });

        expect($sqls[0]['sql'])->toContain('"nick_name" = ?');
        expect($sqls[0]['sql'])->not->toContain('"email"');
    });
});
```

要点：
- 用 `createTablesFor` 建空表即可（不关心数据，只看生成的 SQL）。
- `captureSql` 包住被测代码，返回的数组每项有 `sql`（带 `?`）和 `bindings`。

### 示例 2（模式 C）：mock 一个注入的依赖

场景：测 `OrderService::summary(['status' => 2])` 会不会正确地 `where('status', 2)`、并对 amount 求和。不碰数据库。

```php
<?php
use Kahlan\Plugin\Double;
use Specs\Support\InjectionTarget\OrderService;

describe('OrderService', function () {

    it('按传入状态过滤并对 amount 求和', function () {
        // 1) 造一个假的 DB 依赖
        $db = Double::instance();

        // 2) 声明调用期望（被测代码该调哪些方法、传什么参数）
        expect($db)->toReceive('table')->with('order');
        expect($db)->toReceive('where')->with('status', 2);
        expect($db)->toReceive('sum')->with('amount');

        // 3) 设定链式返回（table/where 返回自身，sum 返回求和值）
        allow($db)->toReceive('table')->andReturn($db);
        allow($db)->toReceive('where')->andReturn($db);
        allow($db)->toReceive('sum')->andReturn(1500);

        // 4) 执行 + 断言返回值（调用期望由 Kahlan 在用例结束时自动校验）
        $service = new OrderService($db);
        $result = $service->summary(['status' => 2]);

        expect($result)->toBe(1500);
    });
});
```

要点：
- `Double::instance()` 造一个接受任意方法调用的假对象。
- `expect($db)->toReceive('table')->with('order')` 声明「应该被调用且参数为 order」，Kahlan 在用例结束自动校验，不满足则失败。
- `allow($db)->toReceive(...)->andReturn(...)` 设定假返回值（链式调用要返回自身）。
- 被测类（`OrderService`）放在 `lara/specs/Support/InjectionTarget/`，命名空间 `Specs\Support\InjectionTarget`。

### 示例 3（模式 A）：测模型的增删改验证规则

场景：测 `BaseModel` 的 `$rules` / `$deleteRules` / `$deleteCascade`。需要真实库（验证失败要真的不写库、级联删除要真的删关联表）。

```php
<?php
use Illuminate\Database\Capsule\Manager as Capsule;
use Specs\Support\FixtureModels\RuleArticle;
use Specs\Support\FixtureModels\RuleNode;
use Specs\Support\FixtureModels\RuleAccount;

describe('模型增删改验证', function () {

    beforeAll(function () {
        // 声明所有表（被测表 + 关联表），精确列定义
        defineTables([
            'spec_rule_article' => fn($t) => [$t->id(), $t->string('title')->nullable(), $t->tinyInteger('status')->default(1)],
            'spec_rule_node'    => fn($t) => [$t->id(), $t->string('name')->nullable()],
            'spec_rule_syslog'  => fn($t) => [$t->id(), $t->integer('userid')],
            'spec_rule_member'  => fn($t) => [$t->id(), $t->integer('account_id')],
        ]);
    });

    beforeEach(function () {
        cleanTables(['spec_rule_article', 'spec_rule_node', 'spec_rule_syslog', 'spec_rule_member']);
        resetSqlBuffer();
    });

    it('增改验证失败时不写库', function () {
        $ent = RuleArticle::create(['title' => '', 'status' => 1]);

        expect($ent->exists)->toBe(false);                                // 模型层：没落库
        expect($ent->getErrors()->get('title')[0])->toBe('标题必填');      // 自定义消息
        expect(Capsule::table('spec_rule_article')->count())->toBe(0);    // 数据库层：兜底
    });

    it('删除验证命中规则时被阻止', function () {
        Capsule::table('spec_rule_node')->insert(['id' => 1, 'name' => 'root']);
        $ent = RuleNode::find(1);  // id=1 命中 not_in:1

        $result = $ent->delete();

        expect($result)->toBe(false);
        expect(RuleNode::find(1))->not->toBeNull();  // 记录仍在
    });

    it('级联删除会先删关联表', function () {
        Capsule::table('spec_rule_member')->insert(['account_id' => 5]);

        $ent = RuleAccount::find(5);
        $sqls = captureSql(function () use ($ent) { $ent->delete(); });

        // 断言真的发出了关联表 delete（绕过模型层，直接看 SQL）
        $cascade = array_filter($sqls, fn($s) => stripos($s['sql'], 'delete from "spec_rule_member"') !== false);
        expect($cascade)->not->toBeEmpty();
    });
});
```

要点：
- 被测模型放 `lara/specs/Support/FixtureModels/`，命名空间 `Specs\Support\FixtureModels`，继承 `Qscmf\Core\BaseModel`。
- **不用手写** Validator/Facade 初始化——bootstrap 已自动完成。
- 验证框架自动生效（watson/validating 监听 saving/deleting 事件），spec 只声明规则、写断言。
- 三层断言互为印证：模型属性（`exists`）+ 库计数（`count`）+ SQL（`captureSql`）。

### 示例 4：测试 ThinkPHP 控制器的 DB 逻辑方法

场景：测 `Admin\Controller\UserController` 的 `applySearchConditions()`（查询条件构建）、`saveUserRole()`（关联维护）等 protected 方法。

控制器的特殊性：父类 `QsController::_initialize()` 在构造时会跑 RBAC、菜单、session，直接 `new UserController()` 或 `Double::instance(['extends'=>...])` 都会触发它（Double 内部也是 `new $class()`）。**必须用 `ReflectionClass::newInstanceWithoutConstructor()` 绕过构造函数**，只测 DB 逻辑。

```php
<?php
use Admin\Controller\UserController;
use App\Models\User;

describe('UserController 数据逻辑方法', function () {

    // 1) 构造绕过 _initialize() 的实例
    $makeController = function (): UserController {
        return (new \ReflectionClass(UserController::class))->newInstanceWithoutConstructor();
    };
    // 2) 反射调用 protected 方法
    $callProtected = function (UserController $c, string $m, array $a = []) {
        $r = (new \ReflectionClass($c))->getMethod($m);
        $r->setAccessible(true);
        return $r->invokeArgs($c, $a);
    };

    beforeAll(function () {
        stubTpRuntime();  // 桩 sysLogs + 安全常量（IS_POST=false）
        defineTables([
            'user'      => fn($t) => [$t->id(), $t->string('nick_name')->nullable(), /* ... */],
            'role'      => fn($t) => [$t->id(), $t->string('name')->nullable(), $t->tinyInteger('status')->default(1)],
            'role_user' => fn($t) => [$t->id(), $t->integer('user_id'), $t->integer('role_id')],
        ]);
    });

    beforeEach(function () {
        cleanTables(['user', 'role', 'role_user']);
        resetSqlBuffer();
    });

    it('nick_name 加模糊条件', function () use ($makeController, $callProtected) {
        $controller = $makeController();
        $query = User::query();
        $callProtected($controller, 'applySearchConditions', [$query, ['nick_name' => 'tider']]);

        $sqls = captureSql(fn() => $query->get());
        expect($sqls[0]['sql'])->toContain('"nick_name" like ?');
        expect($sqls[0]['bindings'])->toBe(['%tider%']);
    });

    it('保存用户角色：删旧建新', function () use ($makeController, $callProtected) {
        \Illuminate\Database\Capsule\Manager::table('role_user')->insert(['user_id' => 1, 'role_id' => 2]);

        $controller = $makeController();
        $callProtected($controller, 'saveUserRole', [1, 3]);  // user 1 从 role 2 改成 role 3

        $rows = \Illuminate\Database\Capsule\Manager::table('role_user')->where('user_id', 1)->get();
        expect($rows->count())->toBe(1);
        expect((int)$rows->first()->role_id)->toBe(3);
    });
});
```

要点：
- **绕过构造函数**：`newInstanceWithoutConstructor()`。**不要** `new`、**不要** `Double::instance(['extends'=>...])`——两者都触发 `_initialize()` 的 RBAC。
- **反射调 protected**：`getMethod()->setAccessible(true)->invokeArgs()`。
- **`stubTpRuntime()`**：桩掉 `sysLogs()`（避免写日志表）+ 定义 `IS_POST`/`MODULE_NAME` 等缺失常量。POST 场景在 `beforeAll` 首次调用即传 `['IS_POST' => true]`（常量不可变）。
- **`I()`/`C()`/`U()` 不桩**：think-core 已提供，I() 读 `$_GET`/`$_POST`，需控制输入时直接设全局变量。
- **只测 DB 逻辑方法**：`add`/`edit`/`forbid` 等 public action 依赖 `error()`/`success()` 的 `qs_exit` + render，属集成测试，放 PHPUnit。
- 参考 `UserControllerSpec.php`。

## Fixture（被测对象）放哪

测试专用的类（造一个简单模型、一个 service）放 `lara/specs/Support/`，按子目录组织，命名空间遵循 `Specs\Support\<子目录>`：

| 命名空间 | 目录 | 用途 |
|----------|------|------|
| `Specs\Support\FixtureModels\` | `Support/FixtureModels/` | 继承 BaseModel 的测试模型（文件名 = 类名） |
| `Specs\Support\InjectionTarget\` | `Support/InjectionTarget/` | 接收依赖注入的 service（供 mock 测试） |

> ⚠️ 文件名必须和类名一致（PSR 规则）：`Specs\Support\FixtureModels\RuleArticle` → `Support/FixtureModels/RuleArticle.php`。

fixture 示例（一个测试模型）：

```php
<?php
namespace Specs\Support\FixtureModels;

use Qscmf\Core\BaseModel;

class RuleArticle extends BaseModel
{
    protected $table = 'spec_rule_article';
    public $timestamps = false;
    protected $guarded = [];

    protected $rules = [
        'title'  => 'required|max:5',
        'status' => 'required|in:0,1',
    ];

    protected $validationMessages = [
        'title.required' => '标题必填',
        // ...
    ];
}
```

## 常见坑

1. **`context()` 不能用**：被 Laravel 11 helper 占用，用嵌套 `describe()` 代替。
2. **改了 bootstrap 后要 `--cc`**：`composer test:kahlan` 用旧缓存会跑出诡异结果。
3. **`createTablesFor` 建的是粗略空表**：只有 `id` + 占位列。需要真实列（插入、sum、过滤）时改用 `defineTables()`。
4. **SQL 带占位符**：`captureSql` 返回的 `sql` 是 `where "id" = ?` 形式，绑定值在 `bindings` 数组里，别在 sql 字符串里找字面值。
5. **测试模型验证无需手写框架初始化**：Validator/Facade 已由 bootstrap 自动初始化。`beforeAll` 只需 `defineTables()`，`beforeEach` 只需 `cleanTables()` + `resetSqlBuffer()`。参考 `ModelRulesSpec.php`。
6. **模式 C（mock）下不要断言数据库状态**：根本没碰库，`exists` / `count` 无意义，只断言调用契约和返回值。
7. **测控制器要绕过 `_initialize()`**：父类构造会跑 RBAC/菜单/session，直接 `new` 或 `Double::instance(['extends'=>...])` 都触发它。用 `ReflectionClass::newInstanceWithoutConstructor()` + 反射调 protected。需 TP 全局函数时调 `stubTpRuntime()`。见示例 4。
8. **IS_POST 等常量定义后不可变**：`stubTpRuntime()` 首次调用即 `define`。POST 场景在 `beforeAll` 首次调用即传 `['IS_POST' => true]`，无法中途切换。
9. **`applySearchConditions` 这类方法若内部触发子查询**（如 role 分支查 role_user），把方法调用包在 `captureSql` 内部，才能同时捕获子查询 SQL。

## 调试技巧

- **只跑一个用例**：用 `fit()`（focus it）代替 `it()`，Kahlan 只跑标记的用例。
- **排除一组**：用 `xdescribe()` 标记的整组会被跳过。
- **打印 SQL**：在 `captureSql` 后 `var_dump($sqls)` 看实际生成的语句。
- **临时排查**：在用例里直接 `echo` / `var_dump`，Kahlan 会把输出附在结果里。

## 参考文件

| 文件 | 说明 |
|------|------|
| `lara/specs/bootstrap.php` | 环境引导 + 辅助函数定义（captureSql/defineTables/cleanTables 等） |
| `lara/specs/kahlan-functions.php` | DSL 垫片（解决 context() 冲突） |
| `kahlan-config.php` | Kahlan 配置入口 |
| `lara/specs/SqlCaptureSpec.php` | 示例：模式 A/B（真实库 + SQL 断言） |
| `lara/specs/InjectionMockSpec.php` | 示例：模式 C（mock 依赖） |
| `lara/specs/ModelRulesSpec.php` | 示例：模型增删改验证（真实库） |
| `lara/specs/UserControllerSpec.php` | 示例：ThinkPHP 控制器 DB 逻辑方法（绕过 _initialize + stubTpRuntime） |
| `lara/specs/Support/` | fixture 模型与被测 service |

## 相关文档

- [Testing.md](./Testing.md) — PHPUnit 集成/功能测试（HTTP、DB 迁移、完整流程）
- [ModelValidation.md](./ModelValidation.md) — 模型验证功能（$rules / $deleteRules / $deleteCascade 的业务用法）
- `lara/specs/AGENTS.md` — Kahlan 目录的索引式参考卡（辅助函数速查表）
