## 测试
在看以下文档时，建议结合qscmf自带的测试用例代码阅读。

#### http测试

http测试实则是模拟接口请求，测试接口逻辑是否与预期一致。

qscmf使用phpunit作为测试框架，在lara\tests下创建测试类，http测试类需要继承Lara\Tests\TestCase类。

```php
<?php
namespace Lara\Tests\Feature;

use Lara\Tests\TestCase;

class AuthTest extends TestCase {

}
```

构造get请求

```php
/**
* @uri 请求url
* @header 自定义请求头 数组类型  例如: ['x-header' => 'value']
* @return 返回请求结果
**/
$this->get($uri, $header);

样例代码 lara/tests/Feature/AuthTest.php
```

构造post请求

```php
/**
* @uri 请求url
* @data 需发送的数据 数组类型  例如: [ 'uid' => 'admin', 'pwd' => '123456']
* 可存放上传文件  例如: [ 'file' => $file ] $file类型必须为SymfonyUploadedFile类型
* @header 自定义请求头 数组类型  例如: ['x-header' => 'value']
* @return 返回请求结果
**/
$this->post($uri, $data, $header);

样例代码 lara/tests/Feature/AuthTest.php
```

模拟超级管理员登录

```php
$this->loginSuperAdmin();
```

模拟普通后台用户登录

```php
/**
* $uid 用户id
**/
$this->loginUser($uid);
```

测试上传文件

```php
//构造的SymfonyUploadedFile类文件对象
$data = [
    'file' => UploadedFile::fake()->image('test.jpg', 100, 100)
];

$query = http_build_query([
    'cate' => 'image',
    'title' => 'test.jpg',
]);
$content = $this->post('api/upload/uploadFile?'.$query, $data);
```

测试数据库是否存在记录

```php
/**
* $tablename 表名
* $where 查询条件 例如: [ 'name' => 'admin', 'status' => '1' ]
**/
$this->assertDatabaseHas($tablename, $where);

样例代码 lara/tests/Feature/UploadTest.php
```

测试数据库是否不存在记录

```php
/**
* $tablename 表名
* $where 查询条件 例如: [ 'name' => 'admin', 'status' => '1' ]
**/
$this->assertDatabaseMissing($tablename, $where);

样例代码 lara/tests/Feature/UserTest.php
```

#### 创建Mock类

如果代码需要请求第三方接口，或者触发一些我们不想在测试里执行的的代码，可以采用Mock类模仿该部分的逻辑，达到只测试接口的目的。

mock类的创建使用phpunit提供的方法

```php
//Foo为需模仿的类,phpunit会自动给我们生成模拟类，方法没有指定返回值，默认返回null
$stub = $this->createMock(Foo::class);

//也可以指定方法的返回值
$stub->method('say')->willReturn(1);

//给Foo类指定Mock实例
app()->instance(Foo::class, $stub);
·
·
·
//业务代码的设计需可测试，如Mock模仿的代码必须封装成类，定义接口解耦逻辑
//用laravel的依赖容器自动构造Foo实例，这样可达到测试实例用Mock实例替换实际业务类的目的
$foo = app()->make(Foo::class);
//该接口方法在测试执行时，会返回我们指定返回的值
$foo->say();

样例代码: lara/tests/Feature/MockTest.php
```

#### Dusk测试

Dusk 是laravel的浏览器自动化测试 工具 ，qscmf将其稍微封装了一下，只需继承Lara\Tests\DuskTestCase类即可使用，具体的使用方法可查看[laravel文档](https://learnku.com/docs/laravel/5.8/dusk/3943)。

样例代码: lara/tests/LoginTest.php

#### 命令行测试

在Testing\TestCase下增加了 cli的命令行模拟执行

```php
$content = $this->cli('app/cliMode', 'Home', 'Controller', 'action', '参数1', '参数2'...);
//content为返回的输出结果
```

#### 队列测试

在Testing\TestCase下增加了执行Job的命令，用于队列测试

```php
$content = $this->runJob('Job类名', 'args 参数');
//content为返回的输出结果
```
样例代码: lara/tests/QueueTest.php


#### 在phpunit里调用tp的代码片段

使用runTp方法，参数接收一个匿名函数，匿名函数可调用tp里的代码，return后可在phpunit接收

```php
$test_ent = $this->runTp(function (){
            return D('Test')->find(1);
        });

$this->assertTrue($test_ent['name'] == '测试');
```

可用该方法测试Tp的代码，但如果只是要验证数据库值，建议还是使用 assertDatabaseHas等测试方法，性能更佳。
