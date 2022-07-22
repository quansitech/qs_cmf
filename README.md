# qscmf

![lincense](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)
[![LICENSE](https://img.shields.io/badge/license-Anti%20996-blue.svg)](https://github.com/996icu/996.ICU/blob/master/LICENSE)
![Pull request welcome](https://img.shields.io/badge/pr-welcome-green.svg?style=flat-square)

## 介绍
快速搭建信息管理类系统的框架。基于tp3.2开发,在其基础上添加了许多功能特性。tp3.2已经停止更新，该框架源码也对核心源码做了部分改动。

## 特性
+ 支持composer依赖管理
+ 支持phpunit及laravel dusk自动化测试
+ 集成laravel数据库管理工具及依赖注入容器
+ 支持ListBuilder、FormBuilder后台管理界面模块化开发
+ 插件系统
+ 简单易用，可自定义的配置管理
+ 消息队列系统
+ 集成Elasticsearch、可自定义索引重建自制，实现数据库记录与搜索引擎索引同步变动

## 截图
<img src="https://user-images.githubusercontent.com/1665649/55472251-36458e80-563e-11e9-87c0-10c386d5bd78.png" />

## 安装
第一种安装方法
```
git clone https://github.com/tiderjian/qs_cmf.git
```

代码拉取完成后，执行composer安装
```
composer install
```

第二种安装方法，composer create project
```php
composer create-project tiderjian/qscmf qscmf
```

完成第一种或者第二种安装后
1. 复制.env.example并重命名为.env，配置数据库参数
2. 执行migrate数据库迁移命令。

```
php artisan migrate
```

将web服务器搭起来后，后台登录地址  协议://域名:端口/admin， 账号:admin 密码:Qs123!@#

## 维护模式
在.env将 APP_MAINTENANCE 设成true，系统进入维护状态，所有请求都只会提示系统维护中。如需要在维护模式下执行升级脚本，可传递"maintenance"给第三个参数
```php
php index.php Qscmf/UpgradeFix/v300FixSchedule/queue/default maintenance
```

## Elasticsearch
框架为集成Elasticsearch提供了方便的方法, 假设使用者已经具备elasticsearch使用的相关知识。

1. 添加 "elasticsearch/elasticsearch": "~6.0" 到composer.json文件，执行composer update 命令安装扩展包。
2. 安装elasticsearch, 具体安装方法自行查找，推荐使用laradock作为开发环境，直接集成了elasticsearch的docker安装环境。
3. 安装ik插件，安装查找elasticsearch官方文档。
4.  在.env下添加 ELASTICSEARCH_HOSTS值，设置为elasticsearch的启动ip和端口，如laradock的默认设置为10.0.75.1:9200，需要配置一组地址，可用“,”隔开。
5. 设置需要使用elastic的model和字段

    以ChapterModel添加title和summary到全文索引为例
    ```php
    // ChapterModel类必须继承接口
    class ChapterModel  extends \Gy_Library\GyListModel implements \Qscmf\Lib\Elasticsearch\ElasticsearchModelContract{
     
       // ElasticsearchHelper已经实现了一些帮助函数
       use \Qscmf\Lib\Elasticsearch\ElasticsearchHelper;
        
       // 初始化全文索引时需要指定该Model要添加的索引记录
       public function elasticsearchIndexList()
       {
            // 如这里chapter表与course表关联，只有当course及chapter状态都为可用，且非描述类chapter(pid = 0为描述类chapter)才会添加全文索引
           return $this->alias('ch')->join('__COURSE__ c ON c.id=ch.course_id and c.status = ' . DBCont::NORMAL_STATUS)->where(['ch.status' => DBCont::NORMAL_STATUS, 'ch.pid' => ['neq', 0]])->field('ch.*')->select();
       }
       
       // chapter进行增删查改时同时会更新索引内容，该方法是指定什么状态的记录才会进行索引更改
       // 返回false为无需索引的记录，true则会进行索引更新
       public function isElasticsearchIndex($ent){
           if($ent['status'] != DBCont::NORMAL_STATUS || $ent['pid'] == 0){
               return false;
           }
      
           $course_ent = D('Course')->find($ent['course_id']);
           if($course_ent['status'] == DBCont::NORMAL_STATUS){
               return true;
           }
           else{
               return false;
           }
       }
      
       // 程序会自动生成索引的配置参数，此处是定义生成参数的规则
       // 以:开头的字母表示该处会自动替换成相应字段的实际值
       // {}表示里面的字符会与替换后的:字段值进行连接，如:id{_chapter}, id实际值为 12，则该处会替换成 12_chapter
       // | 表示可以将字段的实际值传递给指定的函数进行处理，转换成想要的值。如，description字段是富文本内容，我们将html标签进行索引，可以在model方法里自定义一个叫deleteHtmlTag的方法进行处理，当然也可以定义为全局函数，程序会先查找全局是否存在该函数，如果没有再去对象里查找有无该方法
      // index和type的值在建立初始化全文索引时指定，具体查看全文索引初始化说明
       public function elasticsearchAddDataParams()
       {
           return [
               'index' => 'global_search',
               'type' => 'content',
               'id' => ':id{_chapter}',
               'body' => [
                   'title' => ':title',
                   'desc' => ':description|html_entity_decode'
               ]
           ];
       }
    }
    ```
6. 初始化全文索引

    打开Home/Controller/ElasticController.class.php文件, 修改index方法里的$params变量，根据你的需要来设置
    
7. 执行索引初始化，程序会自动检索数据库全部数据表，为需要添加索引的表和字段进行索引添加操作。
    ```
    //进入app目录，下面有个makeIndex.php文件
    php makeIndex.php
    ```
8. 可通过在config文件设置 "ELASTIC_ALLOW_EXCEPTION" 来禁止抛出异常，即使搜索引擎关闭，也不会影响原来的业务操作。
9. 更新操作的索引重建仅会在索引字段发生变化时才会触发。

## Controller
[传送门](https://github.com/quansitech/qs_cmf/blob/master/docs/Controller.md)

## Model
### auto
增加传递新增记录给function或者callback的方法
```php
protected $_auto = array(
    //第六个参数设置为true，可以接收到新增的记录
    //此时如果不需要通过第五个参数额外传递数据，可设置为null
    ['sample_filed', 'sampleCallback', parent::MODEL_INSERT, 'callback', null, true]
);
```

### 封印字段
可设置不可修改和新增的字段

场景：有些字段是由触发器维护的，应用层不应该修改这些字段。但随着系统开发的深入，维护时间的增加。经常会忘记了这个原则，导致了不经意间修改了该字段，产生了难以调试的bug。

用法：
```php
//在model设置 触发器维护字段order_no
protected $_seal_fields = [
    'order_no'
];
```



### 清除数据库缓存

clearCache 方法

参数options 通过该参数可以设置Modal对象的options属性



## 联动删除

在进行一些表删除操作时，很可能要删除另外几张表的特定数据。联动删除功能只需在Model里定义好联动删除规则，在删除数据时即可自动完成另外多张表的删除操作，可大大简化开发的复杂度。

使用样例
```php
//假设有一张文章表Post, 评论表 Message, 点赞表 Like。 
//点赞表有字段type, type_id, 当type=4时，type_id指向文章表的主ID
//评论表有字段post_id，post_id与文章表的主id关联
//这时我们可以在Model里定义 _initialize方法，在该方法内定义 _delete_auto 规则
protected function _initialize() {
    $this->_delete_auto = [
         //实现Message表的联动删除
        ['delete', 'Message', ['id' => 'post_id']],
        //实现点赞表的联动删除，$ent参数就是存放Post表的记录的实体变量
        ['delete', function($ent){
            D('Like')->where(['type' => 4, 'type_id' => $ent['id']])->delete();
        }],
    ];
}

//如需指定删除联动数据失败时的操作，默认为继续删除，请指定第四参数，如:
$this->_delete_auto = [
    ['delete','Message',['id'=>['post_id'],[
        'error_operate'=>self::DELETE_CONTINUE //继续删除
    ]],
    //或
    ['delete','Message',['id'=>['post_id'],[
        'error_operate'=>self::DELETE_BREAK //删除停止并返回错误信息
    ]],
];
```

目前联动删除的定义规则暂时只有两种，第二种规则比第一种规则更灵活，可应用于更多复杂的场景。第一种规则仅能应用在两个表能通过一个外键表达关联的场景。第一种规则在性能上比第二种更优。

## TP数据库
+ 原生sql写法 Db::Raw
> tp会对字符串的sql进行分析，对关键字自动加上``，但做的并不好，会将table方法和field方法的sql解析出语法问题
>
> 因此加入了 Db::Raw的方法来阻止tp解析
> ```php
> D('Test')
> ->table(Db::Raw(( 'SELECT @box_id:=null, @rank:=0 ) a'))
> ->field(Db::Raw('tmp.*, if(@box_id=tmp.box_id, @rank:=@rank+1, @rank:=1) AS rk, @box_id:=tmp.box_id'))
> ```


+ 新增DB_STRICT模式, 在app/Common/Conf/config.php 设置true开启
```php
'DB_STRICT'             =>  env('DB_STRICT', true)
```

strict模式默认启动一下设置

'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'

而非strict模式是

'NO_ENGINE_SUBSTITUTION'

## 数据库迁移
扩展了laravel的迁移功能, 可在执行迁移前后插入一些操作。
```php
class CreateTestTable extends Migration
{

    public function beforeCmmUp()
    {
        echo "执行前置命令" . PHP_EOL;
    }

    public function beforeCmmDown()
    {
        echo "执行前置回滚命令" . PHP_EOL;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test');
    }

    public function afterCmmUp()
    {
        echo "执行后置命令" . PHP_EOL;
    }

    public function afterCmmDown()
    {
        echo "执行后置回滚命令" . PHP_EOL;
    }
}
```

运用场景：

迁移文件是为了方便我们对数据库结构进行变更管理。那么是所有的数据库变更都会放到迁移文件处理吗？当然不是，像一些跟业务逻辑有关的数据处理就不应该放到迁移文件，否则这部分代码跟
业务数据捆绑，很容易导致执行迁移时出错。而只有那些跟业务数据无关，用于构造系统数据存储结构的变更操作才应该放到迁移中。但一个业务系统维护久了，难免必须处理一些数据后才能正常
执行数据库结构变更，例如唯一索引的创建往往就需要我们清理掉一些重复的业务数据。这时就有了不能放在迁移里的业务数据维护脚本的管理需求。

依托上面的场景，就有了在迁移文件中加入了前后置的操作点的构思。默认情况下，只要设置了前后置操作点，执行迁移时就会自动执行一遍，回滚类同。

那么如果只是想执行迁移而不执行前后置操作怎么办呢（例如执行自动测试脚本前，我们会自动构建系统的数据库）。只需在命令后面加入 --no-cmd即可

下面是支持加入--no-cmd操作的命令
```php
php artisan migrate --no-cmd

php artisan migrate:rollback --no-cmd

php artisan migrate:fresh --no-cmd

php artisan migrate:refresh --no-cmd

php artisan migrate:reset --no-cmd
```

+ CmmProcess
该类是为了方便在迁移中调用tp的脚本
> 用法：
>
> ```php
> $process = new \Larafortp\CmmMigrate\CmmProcess();
> //timeout为程序的超时退出时间，默认60秒
> $process->setTimeOut(100)->callTp('/var/www/move/www/index.php', '/home/index/test');
> ```

+ ConfigGenerator
   迁移中处理系统配置的工具类

   addGroup($name) //添加配置分组

   deleteGroup($name) //删除配置分组

   updateGroup($config_name, $group_name)  //将配置转移到指定分组

   以下为新增配置项的操作函数
   > $name 配置名
   > 
   > $title 配置标题
   > 
   > $value 配置值
   > 
   > $remark 配置说明
   > 
   > $group 配置分组
   > 
   > $sort 排序

   addNum($name, $title, $value, $remark = '', $group = 1, $sort = 0) //新增数字类型配置值

   addText($name, $title, $value, $remark = '', $group = 1, $sort = 0) //新增字符类型配置值

   addArray($name, $title, $value, $remark = '', $group = 1, $sort = 0) //新增数组类型配置值

   addPicture($name, $title, $value, $remark = '', $group = 1, $sort = 0) //新增图片类型配置值

   addUeditor($name, $title, $value, $remark = '', $group = 1, $sort = 0) //新增富文本类型配置值

   addSelect($name, $title, $value, $options, $remark = '', $group = 1, $sort = 0) //新增下拉选择配置值 $options 是下拉配置数组

   add($name, $type, $title, $group, $extra, $remark, $value, $sort) //新增配置方法，未预设的第三方组件可使用该函数

   delete($name) //删除配置

## 后台JS
[传送门](https://github.com/quansitech/qs_cmf/blob/master/docs/BackendJs.md)

## ListBuilder
[传送门](https://github.com/quansitech/qs_cmf/blob/master/docs/ListBuilder.md)

## FormBuilder
[传送门](https://github.com/quansitech/qs_cmf/blob/master/docs/FormBuilder.md)

## CompareBuilder
[传送门](https://github.com/quansitech/qs_cmf/blob/master/docs/CompareBuilder.md)

## Builder
[传送门](https://github.com/quansitech/qs_cmf/blob/master/docs/Builder.md)

## Cache
[传送门](https://github.com/quansitech/qs_cmf/blob/master/docs/Cache.md)

## 前台js错误收集
#### 用法
在前端head中引入log.js后调用frontLog方法
```php
    <script src="__PUBLIC__/libs/log.js"></script>
    <script>
      frontLog({
        url:'/api/jsLog/index'
      });
    </script>
```

## 数据库帮助函数
generator
> 低内存消耗迭代函数
>
> 参数 
> 1. $map 查询参数 默认为空数组
> 2. $count 一次查询的数据量，越大占用的内存会大，但运行效率会更高，根据情况灵活调整 默认为 1
>
> 举例
>```php
>
>   foreach(D("Content")->generator([], 200) as $ent){
>      var_dump($ent);
>   }
> 
>```

## 权限功能

[传送门](https://github.com/quansitech/qs_cmf/blob/master/docs/Auth.md)

## 微信登录
为解决第三方平台网站应用的PC扫码后openid不可操作问题，统一对PC端微信扫码以及微信端登录进行封装。
* 从[微信公众平台](https://mp.weixin.qq.com/)中获取公众号的app_id和app_secret，并进行相关配置，放入.env文件
```dotenv
# 微信公众号
WX_APPID=
WX_APPSECRET=
```

* PC扫码页面，在需要显示二维码的地方加入iframe
```html
<iframe src="{:U('qscmf/weixinLogin/scan')}"></iframe>
```
PS:
1. 构造iframe的src时，可通过goto_url参数来指定PC端扫码后跳转的地址，默认为首页
2. 构造iframe的src时，可通过mobile_goto_url参数来指定微信端扫码后跳转的地址，默认为首页

* 微信端获取登录信息
```php
    $wx_info=Qscmf\Lib\WeixinLogin::getInstance()->getInfoForMobile();
```

* 运行/扫码后可用``` session('wx_info') ```获取微信登录信息
* 若'wx_info'的session值已设置，可通过设置config.php中的'WX_INFO_SESSION_KEY'来改变

### 场景模拟
一、 PC端实现扫码登录/注册
* 扫码页面（扫码后需要跳转到'/home/index/wxLogin'）
```html
<!-- 其它代码 -->
    <!-- 此处是放入二维码的位置 -->
    <iframe id="scan" src="{:U('qscmf/weixinLogin/scan',['goto_url'=>urlencode('/home/index/wxLogin')])}"></iframe>
<!-- 其它代码 -->
```
* 登录/注册业务处理（对应上一步的"/home/index/wxLogin"）
```php
$wx_info=json_decode(session('wx_info'),true);
// 若用户表为member表
$member=D('Member')->where(['openid'=>$wx_info['id']])->find();
if ($member){
    //登录
    session('mid',$member['id']);
}else{
    //注册
    $ent=[
        'openid'=>$wx_info['id'],
        'nickname'=>$wx_info['nickname']
    ];
    $r=D('Member')->createAdd($ent);
    if ($r===false){
        E(D('Member')->getError());    
    }
    session('mid',$r);
}
redirect(U('home/user/index'));
```

二、 微信端实现授权登录/注册
1. 授权页面 （授权后需要跳转到'/home/index/wxLogin'）
```php
    $wx_info=Qscmf\Lib\WeixinLogin::getInstance()->getInfoForMobile();
    if ($wx_info){
        redirect(U('/home/index/wxLogin'));    
    }   
```
2. 登录/注册业务处理（对应上一步的"/home/index/wxLogin"）
```php
// 与PC端扫码后登录/注册业务处理一致
```

## 全局函数
[传送门](https://github.com/quansitech/qs_cmf/blob/master/docs/Helper.md)

## js组件
### selectAddr
```blade
select框的地址选择器

参数 
addressLevel: array 省/市/县的select框默认值，默认为：['选择省','选择市','选择区']
level: int 1|2|3 地址的等级：省/市/区，默认为：3
url: array 分别获取地址的接口url，默认为：['/api/area/getProvince.html','/api/area/getCityByProvince.html','/api/area/getDistrictByCity.html']
onSelected: function (val,changeEle){}  每个select框选择地址后执行自定义function，val： 隐藏域的值 changeEle： 触发事件的select
```

代码示例

```php
<input type="hidden" id="hidden_position" name="city_id" value="{$city_id}">
 
<block name="script">
<script type="text/javascript" src="__PUBLIC__/libs/addrSelect/selectAddr.js"></script>
<script>
    jQuery(document).ready(function() {
        $('#hidden_position').selectAddr({
            addressLevel: ['省','市','区'],
            level: 3,
            onSelected: function (val,changeEle){
                log(val);
            }
        });

        function log(str) {
            console.log(str+"-111");
        }    
    });
</script>
</block>
```

## 常量
DOMAIN  域名，可通过env去改写，默认采用$_SERVER["HTTP_HOST"]

ROOT 指定子目录，默认为空, 可通过env改写，如子路径 ROOT=/move

SITE_URL 包含子目录的网站根地址

HTTP_PROTOCOL  返回http或者https协议字符串, 可通过env指定

REQUEST_URI 获取方向代理前的REQUEST_URI值

## 扩展
[传送门](https://github.com/quansitech/qs_cmf/blob/master/docs/Extends.md)

## 消息队列
[传送门](https://github.com/quansitech/qs_cmf/blob/master/docs/Resque.md)

## 测试

在看以下文档时，建议结合qscmf自带的测试用例代码阅读。

#### http测试
http测试实则是模拟接口请求，测试接口逻辑是否与预期一致。

qscmf使用phpunit作为测试框架，在lara\tests下创建测试类，http测试类需要继承Lara\Tests\TestCase类。

````php
<?php
namespace Lara\Tests\Feature;

use Lara\Tests\TestCase;

class AuthTest extends TestCase {
    
}
````

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

$content = $this->post('api/upload/uploadImage', $data);
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

#### 在phpunit里调用tp的代码片段
使用runTp方法，参数接收一个匿名函数，匿名函数可调用tp里的代码，return后可在phpunit接收
```php
$test_ent = $this->runTp(function (){
            return D('Test')->find(1);
        });

$this->assertTrue($test_ent['name'] == '测试');
```
可用该方法测试Tp的代码，但如果只是要验证数据库值，建议还是使用 assertDatabaseHas等测试方法，性能更佳。

#### 压缩前端js代码
压缩办法很多，这里提供一种配置简单的方式，[传送门](https://gist.github.com/gaearon/42a2ffa41b8319948f9be4076286e1f3)

### HEIC格式图片转JPG格式
```
为解决部分组件暂不支持展示heic格式图片，将其转换为jpg

上传到阿里云oss的图片已处理
```
+ 使用
    + 复制数据迁移文件*2022_07_18_014941_alter_file_pic_add_mime_type.php*，qs_file_pic添加字段 mime_type

+ 扩展包需自行注册并实现heic_to_jpg行为
    + 定义行为
      ```php
        class HeicToJpgBehavior{
  
        public function run(&$params)
        {
            // $params为qs_file_pic的一条数据
            // 具体逻辑
            $params['url'] = 'your new url';           
        }
    
        }
      ```
    + 注册行为
      ```php
      \Think\Hook::add('heic_to_jpg', 'xxx\\HeicToJpgBehavior');
      ```

## 文档
由于工作量大，文档会逐步补全。

## lincense
[MIT License](https://github.com/tiderjian/lara-for-tp/blob/master/LICENSE.MIT) AND [996ICU License](https://github.com/tiderjian/lara-for-tp/blob/master/LICENSE.996ICU)
