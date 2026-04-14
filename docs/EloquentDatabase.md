# Eloquent 数据库操作指南

v15 已废弃 ThinkPHP Model 模块，全面使用 Laravel Eloquent ORM。本文档结合项目实际代码编写，展示框架中真实可用的数据库操作方式。

---

## 架构概述

本项目是 ThinkPHP 3.2 + Laravel Eloquent 的混合架构：

- **ThinkPHP** 负责路由、控制器、请求生命周期
- **Laravel Eloquent** 负责数据库操作（ORM + Query Builder）
- **watson/validating** 提供模型自动验证

**数据库连接**：PostgreSQL，表前缀 `qs_`

**Model 文件位置**：`lara/app/Models/`

**命名空间**：`app\Models`（文件内声明小写），引用时使用 `App\Models\Xxx`（大写，与 composer.json PSR-4 配置一致）

```
lara/app/Models/
├── Access.php      # RBAC 权限中间表
├── Config.php      # 系统配置
├── Menu.php        # 后台菜单
├── Node.php        # RBAC 权限节点
├── Post.php        # 文章
├── PostCate.php    # 文章分类
├── Queue.php       # 队列任务
├── Role.php        # 角色
├── RoleUser.php    # 角色-用户中间表
├── Syslogs.php     # 系统日志
└── User.php        # 用户（继承 BaseModel，支持自动验证）
```

---

## 查询方式总览

项目中有两种数据库查询方式：

| 方式 | 适用场景 | 示例 |
|------|---------|------|
| **Eloquent 模型** | 业务表操作，推荐方式 | `User::where('status', 1)->get()` |
| **Capsule::table()** | 无对应模型的表、跨表原始查询 | `Capsule::table('file_pic')->where(...)` |

---

## 基础 Model 定义

### 普通模型

大部分模型直接继承 `Illuminate\Database\Eloquent\Model`：

```php
// lara/app/Models/Role.php
namespace app\Models;

use Illuminate\Database\Eloquent\Model;
use Gy_Library\DBCont;

class Role extends Model
{
    protected $table = 'role';
    public $timestamps = false;
    protected $guarded = [];
}
```

**要点**：
- 必须显式设置 `$table`（不带表前缀，前缀在 `lara/config/database.php` 中统一配置）
- 设置 `$timestamps = false`（本项目大部分表没有 `created_at`/`updated_at` 字段）
- 使用 `$guarded = []` 允许批量赋值（与 ThinkPHP Model 的赋值习惯一致）

### 带验证的模型

需要自动验证的模型继承 `Qscmf\Core\BaseModel`（目前只有 User 模型使用）：

```php
// lara/app/Models/User.php
namespace app\Models;

use Qscmf\Core\BaseModel;

class User extends BaseModel
{
    protected $table = 'user';
    public $timestamps = false;
    protected $guarded = [];

    protected $rules = [
        'nick_name' => 'required|string|max:255|unique:user,nick_name',
        'status'    => 'required|in:0,1',
        'pwd'       => 'required',
        'telephone' => 'required|regex:/^1\d{10}$/',
        'email'     => 'required|email|max:255',
    ];

    protected $validationMessages = [
        'nick_name.required' => '请填写用户名',
        'nick_name.unique'   => '用户名已存在',
        // ...
    ];

    protected $deleteRules = [
        'id' => 'not_in:1|no_related:syslogs,userid',
    ];

    protected $deleteCascade = [
        ['table' => 'role_user', 'foreign_key' => 'user_id'],
    ];
}
```

验证功能详见 [ModelValidation.md](./ModelValidation.md)。

---

## 一、Eloquent 模型查询

### 1.1 基础查询

```php
use App\Models\User;
use App\Models\Role;
use App\Models\Node;

// 按 ID 查找
$user = User::find($id);           // 返回 Model 对象或 null
$userArr = User::getOne($id);      // 返回数组或 null（项目封装方法）

// 条件查询
$roles = Role::where('status', DBCont::NORMAL_STATUS)->get();

// 查询单个字段值
$publishDate = Post::getOneField($id, 'publish_date');
```

> **注意**：`find()` 返回 Eloquent 模型对象，`getOne()` 是项目封装的静态方法，返回 `toArray()` 后的数组。控制器中根据需要选择。

### 1.2 分页查询

**AntdAdmin 控制器** —— 直接使用 Eloquent `paginate()`：

```php
// app/Admin/Controller/UserController.class.php
$users = User::where(function ($query) use ($map) {
    if (!empty($map['nick_name'])) {
        $query->where('nick_name', 'like', "%{$map['nick_name']}%");
    }
    if (isset($map['status']) && $map['status'] !== '') {
        $query->where('status', $map['status']);
    }
})->paginate($rows, ['*'], 'page', $page);

return $this->ajaxReturn([
    'total' => $users->total(),
    'rows'  => $users->items(),
]);
```

**Builder 控制器** —— 使用 `count()` + `offset()/limit()` 配合 GyPage：

```php
// app/Admin/Controller/PostController.class.php
$query = Post::query();
if (isset($get_data['cate_id'])) {
    $query->where('cate_id', $get_data['cate_id']);
}
if (isset($get_data['status'])) {
    $query->where('status', $get_data['status']);
}
if (isset($get_data['key']) && $get_data['word']) {
    $query->where($get_data['key'], 'like', '%' . $get_data['word'] . '%');
}

$count = $query->count();
$page = new \Gy_Library\GyPage($count);

$data_list = $query->orderByRaw('sort asc')
    ->offset(($page->nowPage - 1) * $page->listRows)
    ->limit($page->listRows)
    ->get()
    ->toArray();
```

> **要点**：先通过 `$query->count()` 获取总数，再在同一个 query 上追加 `orderByRaw()->offset()->limit()->get()` 获取分页数据。Eloquent 的 `count()` 不会重置查询构建器的状态。

### 1.3 条件查询构建

使用 `when()` 进行条件筛选：

```php
// NodeController 中的条件查询
$list = Node::query()
    ->when(!empty($map['name']), function ($q) use ($map) {
        $q->where('name', 'like', "%{$map['name']}%");
    })
    ->when(isset($map['status']), function ($q) use ($map) {
        $q->where('status', $map['status']);
    })
    ->orderBy('sort')
    ->paginate($rows, ['*'], 'page', $page);
```

也可以直接链式构建：

```php
$query = PostCate::query();
if (isset($get_data['status'])) {
    $query->where('status', $get_data['status']);
}
if (isset($get_data['key']) && $get_data['word']) {
    $query->where($get_data['key'], 'like', '%' . $get_data['word'] . '%');
}
$data_list = $query->orderByRaw('sort asc')->get()->toArray();
```

### 1.4 排序

```php
// 字符串排序（支持多字段）
Role::orderByRaw('sort asc, id desc')->get();

// 单字段排序
Node::orderBy('sort', 'asc')->get();
```

### 1.5 聚合查询

```php
// 计数
$count = Post::where('status', 1)->count();

// 取单个字段值列表
$roleIds = RoleUser::where('user_id', $userId)->pluck('role_id')->toArray();

// 取键值对
$configs = Config::where('status', 1)->pluck('value', 'name')->toArray();
```

---

## 二、Capsule 直接查询

当操作的表没有对应 Model 时，使用 `Illuminate\Database\Capsule\Manager`：

```php
use Illuminate\Database\Capsule\Manager as Capsule;

// 查询
$files = Capsule::table('file_pic')
    ->where('ref_id', $id)
    ->where('ref_status', 1)
    ->get();

// 原始 SQL
$results = Capsule::select('SELECT * FROM qs_area WHERE level = ?', [1]);

// 删除
Capsule::table('coder_log')->where('id', $id)->delete();
```

> **注意**：本项目只集成了 `illuminate/database`，不是完整 Laravel 框架。不能用 `DB` Facade 或 `Illuminate\Support\Facades\DB`。必须使用 `Capsule::table()` 方式。

---

## 三、写入操作

### 3.1 创建记录

```php
use App\Models\User;
use App\Models\Node;

// 方式一：create() 批量赋值
$user = User::create([
    'nick_name' => 'admin',
    'pwd'       => password_hash('123456', PASSWORD_DEFAULT),
    'email'     => 'admin@example.com',
    'telephone' => '13800138000',
    'status'    => 1,
]);

// 方式二：new + 赋值 + save
$node = new Node();
$node->name = 'Admin';
$node->title = '后台管理';
$node->level = 1;
$node->status = 1;
$node->save();

// 方式三：项目封装的 createAdd()（返回新记录 ID）
$newId = Post::createAdd([
    'title'   => '文章标题',
    'cate_id' => 1,
    'status'  => 1,
]);
```

### 3.2 更新记录

```php
// 方式一：找到后更新
$role = Role::find($id);
$role->name = '新角色名';
$role->save();

// 方式二：批量更新
User::whereIn('id', $ids)->update(['status' => DBCont::FORBIDDEN_STATUS]);

// 方式三：项目封装的 createSave()
Post::createSave([
    'id'     => $id,
    'title'  => '新标题',
    'status' => 1,
]);
```

### 3.3 删除记录

```php
// 单条删除
Role::destroy($id);          // 传 ID
$role = Role::find($id);
$role->delete();              // 传模型实例

// 批量删除
Node::destroy([1, 2, 3]);

// 条件删除
Access::where('role_id', $roleId)->delete();
```

对于使用 BaseModel 的模型（如 User），删除前会自动执行验证和联动删除：

```php
$user = User::find($userId);
if (!$user->delete()) {
    // 删除被阻止，获取原因
    $error = $user->getErrors()->first();
    // 例如："已经产生了系统日记，该用户只能禁用，不能删除"
}
```

---

## 四、模型事件

多个 Model 使用了 Eloquent 事件在写入前后自动处理数据：

### 4.1 creating 事件 — 创建时自动填充

```php
// lara/app/Models/Role.php
protected static function boot()
{
    parent::boot();
    static::creating(function ($model) {
        if (empty($model->status)) {
            $model->status = DBCont::NORMAL_STATUS;
        }
    });
}
```

```php
// lara/app/Models/User.php
static::creating(function ($model) {
    if (empty($model->register_date)) {
        $model->register_date = time();
    }
});
```

### 4.2 saving 事件 — 保存时自动转换

```php
// lara/app/Models/Config.php
static::saving(function ($model) {
    $model->update_time = time();
});
```

```php
// lara/app/Models/Post.php
static::saving(function ($model) {
    if (isset($model->publish_date)
        && is_string($model->publish_date)
        && !is_numeric($model->publish_date)) {
        $model->publish_date = strtotime($model->publish_date);
    }
});
```

### 4.3 自定义事件

新增 Model 时可以按同样模式使用 `boot()` 方法注册事件。支持的事件：`creating`、`created`、`updating`、`updated`、`saving`、`saved`、`deleting`、`deleted`、`restoring`、`restored`。

---

## 五、Scope 查询

Config 模型使用了 Eloquent Scope 封装常用查询条件：

```php
// lara/app/Models/Config.php
public function scopeLists($query)
{
    return $query->where('status', DBCont::NORMAL_STATUS)
                 ->select('type', 'name', 'value');
}

public function scopeGetConfigList($query, array $map)
{
    return $query->where($map)->orderBy('sort');
}
```

**注意**：Scope 方法返回 Query Builder，用于链式调用，不能直接当数组用。项目同时提供了返回数组的静态方法：

```php
// 返回 Query Builder（可链式调用）
$configs = Config::lists()->get();

// 返回数组（直接使用）
$configArray = Config::lists();
// → ['site_name' => 'QS CMF', 'site_url' => '...', ...]
```

---

## 六、中间表操作

项目中的 RBAC 权限涉及多张中间表，操作模式如下：

### 6.1 Access（角色-权限节点）

```php
use App\Models\Access;

// 批量添加权限
Access::createAll([
    ['role_id' => 1, 'node_id' => 10, 'level' => 3, 'module' => 'Admin'],
    ['role_id' => 1, 'node_id' => 11, 'level' => 3, 'module' => 'Admin'],
]);

// 删除角色的全部权限
Access::delAccess(['role_id' => $roleId]);

// 删除指定权限
Access::delAccess(['role_id' => $roleId, 'node_id' => $nodeId]);
```

### 6.2 RoleUser（角色-用户）

```php
use App\Models\RoleUser;

// 获取用户的角色 ID 列表
$roleIds = RoleUser::getRoleIdsByUserId($userId);

// 获取角色下的用户 ID 列表
$userIds = RoleUser::getUserIdsByRoleId($roleId);

// 批量分配角色
RoleUser::createAll([
    ['user_id' => $uid, 'role_id' => 1],
    ['user_id' => $uid, 'role_id' => 2],
]);

// 删除用户的角色关联
RoleUser::where('user_id', $uid)->delete();
```

---

## 七、数据迁移

迁移文件位于 `lara/database/migrations/`，使用 Laravel 标准迁移机制：

```bash
# 运行迁移
php artisan migrate

# 回滚上一次迁移
php artisan migrate:rollback

# 查看迁移状态
php artisan migrate:status
```

### 7.1 PostgreSQL 特殊处理

项目使用 PostgreSQL，主键有特殊宏处理：

```php
// lara/database/macros.php
Blueprint::macro('bigIncrementsForQscmf', function ($column = 'id') {
    $id = $this->bigIncrements($column);
    if (DB::getDriverName() === 'pgsql') {
        $id->generatedAs()->always();  // PostgreSQL ALWAYS GENERATED AS IDENTITY
    }
    return $id;
});
```

用于 `user` 和 `file_pic` 表的主键。新建迁移时可按需使用。

### 7.2 新建迁移示例

```bash
php artisan make:migration add_xxx_to_qs_table --table=table_name
```

---

## 八、从 ThinkPHP Model 迁移

### 8.1 对照表

| ThinkPHP | Laravel Eloquent | 说明 |
|---------|------------------|------|
| `D('User')` | `User::find($id)` | 模型实例化 |
| `M('user')` | `Capsule::table('user')` | 原始表查询 |
| `$model->find($id)` | `Model::find($id)` | 按 ID 查找 |
| `$model->where($map)->select()` | `Model::where(...)->get()` | 条件查询 |
| `$model->add($data)` | `Model::create($data)` | 新增记录 |
| `$model->save($data)` | `$model->update($data)` | 更新记录 |
| `$model->delete($id)` | `Model::destroy($id)` | 删除记录 |
| `$model->count()` | `Model::count()` | 计数 |
| `$model->field('name')->select()` | `Model::select('name')->get()` | 指定字段 |
| `$model->order('sort')` | `Model::orderBy('sort')` | 排序 |
| `$model->limit(10)->page(1)` | `Model::offset(0)->limit(10)` | 分页 |
| `I('title')` | 保持不变（ThinkPHP 输入获取） | — |

### 8.2 迁移示例

**ThinkPHP 原代码：**

```php
$User = D('User');
$map['nick_name'] = array('like', '%admin%');
$map['status'] = 1;
$list = $User->where($map)->order('id desc')->limit(10)->select();
```

**迁移后 Eloquent 代码：**

```php
$list = User::where('nick_name', 'like', '%admin%')
    ->where('status', 1)
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get()
    ->toArray();
```

**带分页的完整迁移（配合 GyPage）：**

```php
// 构建查询条件
$query = Post::query();
if (isset($get_data['cate_id'])) {
    $query->where('cate_id', $get_data['cate_id']);
}
if (isset($get_data['status'])) {
    $query->where('status', $get_data['status']);
}

// 获取总数并分页
$count = $query->count();
$page = new \Gy_Library\GyPage($count);

// 获取当前页数据
$data_list = $query->orderByRaw('sort asc')
    ->offset(($page->nowPage - 1) * $page->listRows)
    ->limit($page->listRows)
    ->get()
    ->toArray();
```

### 8.3 新建 Model 的模板

```php
<?php

namespace app\Models;

use Gy_Library\DBCont;
use Illuminate\Database\Eloquent\Model;

class Example extends Model
{
    protected $table = 'example';
    public $timestamps = false;
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->status)) {
                $model->status = DBCont::NORMAL_STATUS;
            }
        });
    }

    public static function getOne(int $id): ?array
    {
        $record = self::find($id);
        return $record ? $record->toArray() : null;
    }

    public static function createAdd(array $data)
    {
        $record = self::create($data);
        return $record ? $record->id : false;
    }

    public static function createSave(array $data)
    {
        if (isset($data['id'])) {
            return self::where('id', $data['id'])->update($data);
        }
        return false;
    }
}
```

---

## 九、注意事项

### 9.1 命名空间大小写

模型文件内声明 `namespace app\Models;`（小写 `a`），但引用时必须使用 `use App\Models\Xxx;`（大写 `A`），因为 `composer.json` 配置的是 `"App\\": "lara\/app"`。不一致会导致 `Class not found` 错误。

### 9.2 表前缀

Model 的 `$table` 属性不要写表前缀。表前缀在 `lara/config/database.php` 中统一配置为 `qs_`，Eloquent 自动拼接。

### 9.3 不要使用 Laravel Facade

本项目的 Eloquent 是通过 Capsule Manager 独立集成的，不是完整 Laravel 应用。以下写法不可用：

```php
// 错误 - Facade 不可用
use Illuminate\Support\Facades\DB;
DB::table('user')->get();

// 正确 - 使用 Capsule Manager
use Illuminate\Database\Capsule\Manager as Capsule;
Capsule::table('user')->get();
```

### 9.4 状态常量

使用 `Gy_Library\DBCont` 中的常量，不要硬编码数字：

```php
use Gy_Library\DBCont;

DBCont::NORMAL_STATUS    // 1 - 正常
DBCont::FORBIDDEN_STATUS // 0 - 禁用
DBCont::PUBLISH_STATUS   // 1 - 已发布
DBCont::UNPUBLISH_STATUS // 0 - 未发布
```

### 9.5 序列化陷阱

不要将 Eloquent Query Builder 对象放入缓存。Query Builder 包含闭包，不可序列化：

```php
// 错误 - scope 返回的是 Query Builder，包含闭包
$configs = Config::scopeLists()->get();  // Builder 对象
S('config_cache', $configs); // Serialization of 'Closure' is not allowed

// 正确 - 调用静态方法返回纯数组
$configs = Config::lists();  // 静态方法 lists() 返回 array
S('config_cache', $configs); // OK
```

### 9.6 get() vs first()

```php
// 多条记录
$users = User::where('status', 1)->get();       // 返回 Collection
$users = User::where('status', 1)->get()->toArray(); // 返回二维数组

// 单条记录
$user = User::where('email', $email)->first();   // 返回 Model 或 null
$user = User::where('email', $email)->first()?->toArray(); // 返回数组或 null
```
