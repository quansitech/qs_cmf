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
+ 支持Listbuilder、Formbuilder后台管理界面模块化开发
+ 插件系统
+ 简单易用，可自定义的配置管理
+ 消息队列系统
+ 集成Elasticsearch、可自定义索引重建自制，实现数据库记录与搜索引擎索引同步变动

## 截图
<img src="https://user-images.githubusercontent.com/1665649/55472251-36458e80-563e-11e9-87c0-10c386d5bd78.png" />

## 安装
```
git clone https://github.com/tiderjian/qs_cmf.git
```

代码拉取完成后，执行composer安装
```
composer install
```

1. 复制.env.example并重命名为.env，配置数据库参数
2. 执行migrate数据库迁移命令。

```
php artisan migrate
```

将web服务器搭起来后，后台登录地址  协议://域名:端口/admin， 账号:admin 密码:admin123

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
    class ChapterModel  extends \Gy_Library\GyListModel implements \Common\Lib\ElasticsearchModelContract{
 
       // ElasticsearchHelper已经实现了一些帮助函数
       use \Common\Lib\ElasticsearchHelper;
        
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
```

目前联动删除的定义规则暂时只有两种，第二种规则比第一种规则更灵活，可应用于更多复杂的场景。第一种规则仅能应用在两个表能通过一个外键表达关联的场景。第一种规则在性能上比第二种更优。

## Listbuilder
### xlsx导出excel
由后端完成excel导出操作会极大占用服务器资源，同时数据太多时往往会需要处理很长时间，页面长时间处于卡死状态用户体验也极差，因此采用前端分批导出excel数据才是更合理的做法。

使用样例
```php
.
.
.

class PostController extends GyListController{
    //继承ExportExcelByXlsx
    use ExportExcelByXlsx;
.
.
.
    
        $builder = new \Common\Builder\ListBuilder();
        //第一个参数指定export类型，第二个参数是指定需要覆盖的html组件属性
        //title为按钮名称，默认导出excel
        //data-url为点击导出按钮后ajax请求的地址,必填
        //data-filename 为生成的excel文件名,默认为浏览器的默认生成文件名
        //data-streamrownum 为每次请求获取的数据数
        $builder->addTopButton('export', array('title' => '样例导出', 'data-url' => U('/admin/post/export'), 'data-filename' => '文章列表', 'data-streamrownum' => '10'));
    
.
.
.
    //导出excel请求的action
    public function export(){
    
        //exportExcelByXlsx为 ExportExcelByXlsx trait提供的方法
        //参数为一个闭包函数，接收两个参数， page为请求的页数， rownnum为请求的数据行数
        $this->exportExcelByXlsx(function($page, $rownum){
             //闭包函数必须返回如下数据格式
             //[
             //    [  'excel表头1' =>  行1数据1, 'excel表头2' => 行1数据2 ..... ]
             //    [  'excel表头1' =>  行2数据1, 'excel表头2' => 行2数据2 ..... ]
             //    ...
             //]
            return [
                 [  '姓名' =>  'tt', '性别' => 'male', '年龄' => 23 ]
                 [  '姓名' =>  'ff', '性别' => 'female', '年龄' => 19 ]
            ];
        });

    }

```

## Formbuilder

#### qiniu_audio/qiniu_video组件

```php
$options = [
    'multiple' => true, //是否开启多文件上传 默认关闭
    'url' => U('api/qiniu/upToken', ['type' => 'bigaudio']), //重置uptoken的获取地址，或者修改类型设置 type都可以直接修改url属性   默认地址为 U('api/qiniu/upToken', ['type' => 'audio'])
];
//如没有特别需求，$options可不传
addFormItem('audio_id', 'qiniu_audio', '音频文件', '', $options)
```

设置.env 七牛的ak 和 sk
```blade
QINIU_AK=**********
QINIU_SK=************
```

修改/app/Common/Conf/config.php，配置上传类型
```php
//UPLOAD_TYPE_*** 其中***为对应的type
'UPLOAD_TYPE_VIDEO' => array(
        'mimes'    => 'video/mp4,video/webm', //允许上传的文件MiMe类型，多个值用逗号分隔
        'maxSize'  => 500*1024*1024, //上传的文件大小限制
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，>多个参数使用数组
        'pfopOps' => "avthumb/mp4/ab/160k/ar/44100/acodec/libfaac/r/30/vb/2400k/vcodec/libx264/s/1280x720/autoscale/1/stripmeta/0", //七牛转码策略
        'pipeline' => 'video', //处理管道
        'bucket' => 'video', //七牛bucket
        'domain' => 'https://***.qscmf.test' //上传至七牛后，访问文件的domain
)
```

## Builder

#### setNID 

```blade
参数 
$nid  需要高亮的左菜单栏的node_id
```

#### setNIDByNode
```blade
该方法是setNID的封装，通过module controller action动态获取nid

参数 
$module 需要高亮左侧菜单的module_name
$controller 需要高亮左侧菜单的controller_name
$action 需要高亮左侧菜单的action_name
```


## 全局函数

#### showThumbUrl

```php
参数
$file_id 存放在qs_file_pic表的图片id
$prefix 缩略图的前缀，与裁图插件的前缀相对应
$replace_img 如获取图片失败，适应该指定的图片url代替

返回值
图片url地址

该函数一般用于获取裁剪插件所裁出来的本地缩略图。
如果$file_id对应的是用seed功能填充出来的图片，还可以依据前缀获取到所希望图片的大小，自动构造相同的大小的图片url。
使用该函数即可在不做任何代码改动的情况下完好的作用在本地图片上传和填充伪造图片的两种场景。
```

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

## 文档
由于工作量大，文档会逐步补全。

## lincense
[MIT License](https://github.com/tiderjian/lara-for-tp/blob/master/LICENSE.MIT) AND [996ICU License](https://github.com/tiderjian/lara-for-tp/blob/master/LICENSE.996ICU)
