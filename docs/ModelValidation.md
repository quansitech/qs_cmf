# Laravel 模型验证功能文档

## 概述

本项目基于 `watson/validating` 扩展包，为 Laravel Eloquent 模型添加了自动验证功能，支持创建、更新和删除时的数据验证。

**主要特性：**
- ✅ 自动触发验证（通过模型事件）
- ✅ 统一的错误处理（返回 false，通过 `getErrors()` 获取错误）
- ✅ 支持自定义验证规则
- ✅ 删除前验证（检查关联表）
- ✅ 联动删除（自动删除关联数据）
- ✅ ThinkPHP 兼容（类似 `$_validate`、`$_delete_validate` 和 `$_delete_auto`）

---

## 基础模型（BaseModel）

所有 Laravel 模型都应继承 `BaseModel`，自动获得验证功能：

```php
<?php
namespace app\Models;

class User extends BaseModel
{
    protected $table = 'user';

    // 验证规则
    protected $rules = [
        'nick_name' => 'required|string|max:255|unique:user,nick_name',
        'email' => 'required|email',
    ];

    // 删除验证规则
    protected $deleteRules = [
        'id' => 'not_in:1|no_related:syslogs,userid',
    ];

    // 删除验证错误消息
    protected $deleteValidationMessages = [
        'id.not_in' => '不能删除超级管理员账户',
        'id.no_related' => '已经产生了系统日记，该用户只能禁用，不能删除',
    ];

    // 联动删除配置
    protected $deleteCascade = [
        ['table' => 'role_user', 'foreign_key' => 'user_id'],
    ];

    // 验证错误消息
    protected $validationMessages = [
        'nick_name.required' => '请填写用户名',
        'email.email' => '邮箱格式不正确',
    ];
}
```

---

## 1. 创建和更新验证

### 1.1 定义验证规则

```php
protected $rules = [
    'nick_name' => 'required|string|max:255|unique:user,nick_name',
    'status' => 'required|in:0,1',
    'pwd' => 'required|string|min:6|max:12',
    'telephone' => 'required|regex:/^1\d{10}$/',
    'email' => 'required|email|max:255',
];
```

### 1.2 定义错误消息

```php
protected $validationMessages = [
    'nick_name.required' => '请填写用户名',
    'nick_name.unique' => '用户名已存在',
    'status.required' => '必选填写用户状态',
    'status.in' => '状态值超出范围',
    'pwd.required' => '密码必填',
    'pwd.min' => '密码长度必须在6-12位',
    'pwd.max' => '密码长度必须在6-12位',
    'telephone.required' => '手机号码必填',
    'telephone.regex' => '手机号码格式不正确',
    'email.required' => '电子邮箱必填',
    'email.email' => '邮箱格式不正确',
];
```

### 1.3 使用示例

```php
// 创建用户
$user = new User();
$user->nick_name = 'test_user';
$user->pwd = '123456';
$user->telephone = '13800138000';
$user->email = 'invalid-email';  // 错误的邮箱格式
$user->status = 1;

$result = $user->save();

if ($result === false) {
    echo $user->getErrors()->first();
    // 输出：邮箱格式不正确
} else {
    echo '保存成功';
}

// 批量赋值
User::create([
    'nick_name' => 'test_user',
    'pwd' => '123456',
    'telephone' => '13800138000',
    'email' => 'test@example.com',
    'status' => 1,
]);
// 验证失败时自动抛出异常或返回 false
```

---

## 2. 删除前验证

类似 ThinkPHP 的 `$_delete_validate`，在删除模型前自动验证。

### 2.1 内置验证规则

| 规则 | 用法 | 说明 |
|------|------|------|
| `not_in` | `not_in:1,2,3` | 字段值不能在指定列表中 |
| `no_related` | `no_related:表名,外键` | 关联表中不能存在记录 |

### 2.2 定义删除验证规则

```php
protected $deleteRules = [
    'id' => 'not_in:1',                       // 不能删除 ID 为 1 的超级管理员
    'id' => 'no_related:syslogs,userid',      // 检查是否有系统日志
];

protected $deleteValidationMessages = [
    'id.not_in' => '不能删除超级管理员账户',
    'id.no_related' => '已经产生了系统日记，该用户只能禁用，不能删除',
];
```

### 2.3 `no_related` 自定义规则

**格式：** `no_related:数据库表名,外键字段名`

**作用：** 检查关联表中是否存在引用当前模型的记录，如果存在则禁止删除。

**示例：**

```php
// User 模型
protected $deleteRules = [
    'id' => 'no_related:syslogs,userid',  // 检查 syslogs 表中是否有 userid = 当前用户 ID 的记录
];

// Role 模型
protected $deleteRules = [
    'id' => 'no_related:access,role_id',      // 检查 access 表
    'id' => 'no_related:role_user,role_id',  // 检查 role_user 表
];

// Menu 模型
protected $deleteRules = [
    'id' => 'no_related:node,menu_id',  // 检查 node 表
];

// Config 模型
protected $deleteRules = [
    'group' => 'not_in:2',  // 不能删除 group=2 的系统配置
];
```

### 2.4 使用示例

```php
// 删除用户
$user = User::find($userId);
$result = $user->delete();

if ($result === false) {
    echo $user->getErrors()->first();
    // 输出：已经产生了系统日记，该用户只能禁用，不能删除
} else {
    echo '删除成功';
}

// 组合规则检查
protected $deleteRules = [
    'id' => 'not_in:1|no_related:syslogs,userid',  // 多个规则用 | 分隔
];

// 先检查 not_in，再检查 no_related，任一规则失败都会阻止删除
```

---

## 3. 联动删除

类似 ThinkPHP 的 `$_delete_auto`，删除模型时自动删除关联表中的数据。

### 3.1 定义联动删除规则

```php
protected $deleteCascade = [
    ['table' => 'role_user', 'foreign_key' => 'user_id'],
    ['table' => 'user_profile', 'foreign_key' => 'user_id'],
];
```

**配置说明：**
- `table` - 关联表的数据库表名
- `foreign_key` - 关联表中的外键字段名

### 3.2 工作原理

当执行 `$model->delete()` 时：

```
1. 触发 deleting 事件
   ↓
2. 执行 validateBeforeDelete()
   - 验证删除规则（$deleteRules）
   - 验证失败返回 false，阻止删除
   ↓
3. 验证通过后执行 cascadeDelete()
   - 遍历 $deleteCascade 配置
   - 删除关联表中符合条件的记录
   ↓
4. 删除模型本身
```

### 3.3 使用示例

**User 模型：**

```php
class User extends BaseModel
{
    protected $deleteCascade = [
        ['table' => 'role_user', 'foreign_key' => 'user_id'],  // 删除用户时，自动删除角色关联
    ];
}

// 使用
$user = User::find($userId);
$user->delete();  // 自动删除 role_user 表中 user_id = $userId 的所有记录
```

**Role 模型：**

```php
class Role extends BaseModel
{
    protected $deleteCascade = [
        ['table' => 'role_user', 'foreign_key' => 'role_id'],  // 删除角色关联
        ['table' => 'access', 'foreign_key' => 'role_id'],      // 删除权限关联
    ];
}
```

**Menu 模型：**

```php
class Menu extends BaseModel
{
    protected $deleteCascade = [
        ['table' => 'node', 'foreign_key' => 'menu_id'],  // 删除菜单时，自动删除关联的节点
    ];
}
```

### 3.4 与 ThinkPHP 对比

| ThinkPHP | Laravel (BaseModel) | 说明 |
|---------|---------------------|------|
| `$_delete_auto` | `$deleteCascade` | 联动删除配置 |
| `array('delete', 'RoleUser', array('id' => 'user_id'))` | `['table' => 'role_user', 'foreign_key' => 'user_id']` | 配置格式 |
| 需要指定操作类型（delete） | 自动执行删除操作 | Laravel 更简洁 |

**ThinkPHP 迁移示例：**

```php
// ThinkPHP
protected $_delete_auto = array(
    array('delete', 'RoleUser', array('id' => 'user_id')),
    array('delete', 'UserProfile', array('id' => 'user_id')),
);

// Laravel
protected $deleteCascade = [
    ['table' => 'role_user', 'foreign_key' => 'user_id'],
    ['table' => 'user_profile', 'foreign_key' => 'user_id'],
];
```

### 3.5 完整示例：验证 + 联动删除

```php
class User extends BaseModel
{
    protected $table = 'user';

    // 删除验证：检查是否可以删除
    protected $deleteRules = [
        'id' => 'not_in:1',                         // 不能删除超级管理员
        'id' => 'no_related:syslogs,userid',        // 不能删除有日志的用户
    ];

    protected $deleteValidationMessages = [
        'id.not_in' => '不能删除超级管理员账户',
        'id.no_related' => '已经产生了系统日记，该用户只能禁用，不能删除',
    ];

    // 联动删除：删除用户时自动清理关联数据
    protected $deleteCascade = [
        ['table' => 'role_user', 'foreign_key' => 'user_id'],  // 删除角色关联
    ];
}

// 使用流程
$user = User::find($userId);

// 第一步：验证是否可以删除
// - 检查 ID 是否为 1
// - 检查是否有系统日志
if (!$user->delete()) {
    echo '删除失败：' . $user->getErrors()->first();
    exit;
}

// 第二步：验证通过后自动执行联动删除
// - 删除 role_user 表中的关联记录

// 第三步：删除用户本身
echo '用户及关联数据已删除';
```

### 3.6 测试联动删除

项目提供了测试方法：

```bash
# 测试联动删除功能
php ./www/index.php /home/test/cascadeDelete
```

**测试内容：**
1. 创建测试用户
2. 添加 role_user 关联记录
3. 删除用户
4. 验证 role_user 记录是否被自动删除

**预期输出：**

```
=== 测试联动删除功能 ===

测试 1: 删除用户时自动删除 role_user 关联
----------------------------------------
创建测试用户，ID: X
为用户添加角色关联，关联记录数: 1
✅ 用户删除成功
✅ 联动删除成功，role_user 记录已自动删除

=== 测试完成 ===
```

### 3.7 注意事项

⚠️ **谨慎使用联动删除**

1. **优先使用数据库外键约束**
   ```sql
   -- 更推荐的方式：在数据库层面定义
   ALTER TABLE role_user ADD CONSTRAINT fk_user_id
   FOREIGN KEY (user_id) REFERENCES user(id)
   ON DELETE CASCADE;
   ```

2. **考虑使用软删除**
   ```php
   use Illuminate\Database\Eloquent\SoftDeletes;

   class User extends BaseModel
   {
       use SoftDeletes;
       protected $dates = ['deleted_at'];
   }
   ```

3. **生产环境前充分测试**
   - 联动删除会永久删除数据
   - 确保没有遗漏重要的关联表
   - 建议在测试环境验证

4. **执行顺序**
   - 先验证（validateBeforeDelete）
   - 验证通过后执行联动删除（cascadeDelete）
   - 最后删除模型本身
   - 任一环节失败都会阻止后续操作

---

## 4. 验证规则详解

### 4.1 Laravel 内置验证规则

[完整验证规则列表](https://laravel.com/docs/validation#available-validation-rules)

常用规则：

| 规则 | 说明 | 示例 |
|------|------|------|
| `required` | 必填 | `required` |
| `email` | 邮箱格式 | `email` |
| `unique` | 唯一性 | `unique:user,nick_name` |
| `in` | 在指定值中 | `in:0,1` |
| `not_in` | 不在指定值中 | `not_in:1,2,3` |
| `regex` | 正则表达式 | `regex:/^1\d{10}$/` |
| `min` | 最小长度 | `min:6` |
| `max` | 最大长度 | `max:255` |
| `between` | 在范围之间 | `between:6,12` |

### 4.2 自定义规则：`no_related`

**语法：** `no_related:表名,外键字段`

**工作原理：**

```php
// BaseModel 中实现
$validator->extend('no_related', function ($attribute, $value, $parameters) {
    $tableName = $parameters[0];    // 数据库表名
    $foreignKey = $parameters[1];   // 外键字段名

    $count = \Illuminate\Database\Capsule\Manager::table($tableName)
        ->where($foreignKey, $this->id)
        ->count();

    return $count === 0;  // 无记录返回 true，有记录返回 false
});
```

**使用场景：**

- 不能删除有订单的用户
- 不能删除有子菜单的菜单
- 不能删除有文章的分类
- 不能删除有权限分配的角色

---

## 5. 与 ThinkPHP 对比

| ThinkPHP | Laravel (BaseModel) | 说明 |
|---------|---------------------|------|
| `$_validate` | `$rules` + `$validationMessages` | 验证规则配置 |
| `$_delete_validate` | `$deleteRules` + `$deleteValidationMessages` | 删除验证配置 |
| `$_delete_auto` | `$deleteCascade` | 联动删除配置 |
| `parent::EXISTS_VALIDATE` | `no_related:表名,外键` | 检查关联表是否存在记录 |
| `parent::NOT_ALLOW_VALUE_VALIDATE` | `not_in:1,2,3` | 字段值不能在指定列表中 |
| `create()` 返回 false | `$model->save()` 返回 false | 验证失败统一返回 false |
| `$model->getError()` | `$model->getErrors()` | 获取错误信息 |

**ThinkPHP 迁移示例：**

```php
// ThinkPHP
protected $_delete_validate = array(
    array('Syslogs', 'userid', parent::EXIST_VALIDATE, '已经产生了系统日记'),
    array(array('1'), 'id', parent::NOT_ALLOW_VALUE_VALIDATE, '不能删除超级管理员'),
);

// Laravel
protected $deleteRules = [
    'id' => 'not_in:1|no_related:syslogs,userid',
];

protected $deleteValidationMessages = [
    'id.not_in' => '不能删除超级管理员',
    'id.no_related' => '已经产生了系统日记',
];

// ThinkPHP
protected $_delete_auto = array(
    array('delete', 'RoleUser', array('id' => 'user_id')),
);

// Laravel
protected $deleteCascade = [
    ['table' => 'role_user', 'foreign_key' => 'user_id'],
];
```

---

## 6. 完整示例

### 6.1 User 模型

```php
<?php
namespace app\Models;

class User extends BaseModel
{
    protected $table = 'user';
    public $timestamps = false;

    // 创建和更新验证规则
    protected $rules = [
        'nick_name' => 'required|string|max:255|unique:user,nick_name',
        'status' => 'required|in:0,1',
        'pwd' => 'required|string|min:6|max:12',
        'telephone' => 'required|regex:/^1\d{10}$/',
        'email' => 'required|email|max:255',
    ];

    // 创建和更新验证消息
    protected $validationMessages = [
        'nick_name.required' => '请填写用户名',
        'nick_name.unique' => '用户名已存在',
        'status.required' => '必选填写用户状态',
        'status.in' => '状态值超出范围',
        'pwd.required' => '密码必填',
        'pwd.min' => '密码长度必须在6-12位',
        'pwd.max' => '密码长度必须在6-12位',
        'telephone.required' => '手机号码必填',
        'telephone.regex' => '手机号码格式不正确',
        'email.required' => '电子邮箱必填',
        'email.email' => '邮箱格式不正确',
    ];

    // 删除验证规则
    protected $deleteRules = [
        'id' => 'not_in:1|no_related:syslogs,userid',
    ];

    // 删除验证消息
    protected $deleteValidationMessages = [
        'id.not_in' => '不能删除超级管理员账户',
        'id.no_related' => '已经产生了系统日记，该用户只能禁用，不能删除',
    ];
}
```

### 5.2 使用示例

```php
// 创建用户
$user = new User();
$user->nick_name = 'test_user';
$user->pwd = '123456';
$user->telephone = '13800138000';
$user->email = 'test@example.com';
$user->status = 1;

if (!$user->save()) {
    echo '创建失败：' . $user->getErrors()->first();
}

// 删除用户
$user = User::find($userId);
if (!$user->delete()) {
    echo '删除失败：' . $user->getErrors()->first();
}
```

---

## 7. 测试验证功能

项目提供了测试控制器来验证功能：

```bash
# 测试创建和更新验证
php ./www/index.php /home/test/validation

# 测试删除验证
php ./www/index.php /home/test/deleteValidation

# 调试验证配置
php ./www/index.php /home/test/debug
```

测试位置：`app/Home/Controller/TestController.class.php`

---

## 8. 注意事项

### 8.1 ThinkPHP 混合环境

- 使用 `\Illuminate\Database\Capsule\Manager::table()` 查询数据库，避免与 ThinkPHP 的 `DB` 类冲突
- 通过 `BaseModel` 统一处理 ThinkPHP 兼容性问题

### 8.2 unique 规则自动处理

`watson/validating` 会自动处理更新时的 unique 验证，无需手动排除当前 ID：

```php
// 创建时
'user_id' => 'unique:users,user_id'  // 检查所有记录

// 更新时自动转换为
'user_id' => 'unique:users,user_id,' . $user->id  // 排除当前记录
```

### 8.3 错误处理

- 验证失败统一返回 `false`
- 通过 `$model->getErrors()->first()` 获取第一条错误
- 通过 `$model->getErrors()->all()` 获取所有错误
- 错误信息返回 `Illuminate\Support\MessageBag` 对象

### 8.4 删除验证顺序

多个规则按顺序执行，任一失败都会阻止删除：

```php
protected $deleteRules = [
    'id' => 'not_in:1|no_related:syslogs,userid',
];

// 先检查 not_in:1，失败则停止
// 再检查 no_related:syslogs,userid
```

---

## 9. 常见问题

### Q1：如何禁用验证？

```php
// 跳过验证保存
$user->save(['validating' => false]);

// 或使用 forceSave
$user->forceSave();
```

### Q2：如何手动验证？

```php
if (!$user->isValid()) {
    echo $user->getErrors()->first();
}
```

### Q3：验证失败会抛出异常吗？

不会。本项目统一返回 `false`，通过 `getErrors()` 获取错误信息。

### Q4：如何自定义验证规则？

在 `BaseModel` 中添加自定义规则：

```php
$validator->extend('custom_rule', function ($attribute, $value, $parameters) {
    // 自定义验证逻辑
    return true/false;
});
```

---

## 10. 相关文档

- [watson/validating 官方文档](https://github.com/watson/validating)
- [Laravel 验证文档](https://laravel.com/docs/validation)
- [ThinkPHP 验证规则](https://www.thinkphp.cn/topic/6653.html)
- 项目文档：`CLAUDE.md` - ThinkPHP + Laravel 混合架构说明
