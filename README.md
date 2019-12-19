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

将web服务器搭起来后，后台登录地址  协议://域名:端口/admin， 账号:admin 密码:admin123

## 维护模式
在.env将 APP_MAINTENANCE 设成true，系统进入维护状态，所有请求都只会提示系统维护中。如需要在维护模式下执行升级脚本，可传递"maintenance"给第三个参数
```php
php index.php Qscmf/UpgradeFix/v300FixSchedule/queue/default maintenance
```

## Excel操作类

[传送门](https://github.com/tiderjian/qs_cmf/blob/master/docs/QsExcel.md)

## imageproxy
[imageproxy](https://github.com/willnorris/imageproxy) 是个图片裁剪、压缩、旋转的图片代理服务。框架集成了imageproxy全局函数来处理图片地址的格式化，通过.env来配置地址格式来处理不同环境下imageproxy的不同配置参数

+ env的地址格式配置
```blade
IMAGEPROXY_URL={schema}://{domain}/ip/{options}/{remote_uri}
```
+ 占位符替换规则
```
占位符用{}包裹
schema 当前地址的协议类型 http 或者 https
domain 当前网站使用的域名
options 图片处理规则 https://godoc.org/willnorris.com/go/imageproxy#ParseOptions
remote_uri 代理的图片uri，如果外网图片，该占位符会替换成该地址，否则是网站图片的uri
path 网站图片的相对地址，如 http://localhost/Uploads/image/20190826/5d634f5f6570f.jpeg，path则为Uploads/image/20190826/5d634f5f6570f.jpeg
```

+ imageproxy全局函数
```php
// imageproxy图片格式处理
//options 图片处理规则
//file_id 图片id
// return 返回与.env配置格式对应的图片地址
imageproxy($options, $file_id)

如 IMAGEPROXY_URL={schema}://{domain}/ip/{options}/{remote_uri}
imageproxy('100x150', 1)
返回地址 http://localhost/ip/100x150/http://localhost/Uploads/image/20190826/5d634f5f6570f.jpeg

如 IMAGEPROXY_URL={schema}://{domain}/ip/{options}/{path} (这种格式通常配合imageproxy -baseURL使用)
返回地址 http://localhost/ip/100x150/Uploads/image/20190826/5d634f5f6570f.jpeg
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

## 后台JS
#### 跳转js执行机制

```php
可通过$this->success('修改成功', 'javascript:js代码') 的方式，让页面提交后执行对应的js代码。
该方法仅对使用ajax-post和ajax-form的页面有效。

使用场景举例：
当翻页后，对列表数据进行编辑操作，编辑成功后希望页面跳回之前的页码。

$this->success('修改成功', 'javascript:location.href=document.referrer;');
```

#### 获取当前列表选中checkbox的值
```blade
var ids = $(".check-all").data('checkedIds');
```

#### select2_ajax
```blade
根据API重置select2组件的值

参数
select_dom：dom select2元素节点
url：string 获取数据的API，需返回数据格式：['total_count' => 12,'data' => [['id' => 1, 'text' => 'text']]]
query：json 筛选API的参数，search为dom的值，pageSize最小值为20，最少会包括的参数有：search=xxx&page=1&pageSize=20,数据格式:{pageSize: "{:C('ADMIN_PER_PAGE_NUM')}"}
```

代码示例
```php
    // API
    public function getLibrary($city){
        $search = I('search');
        $page = I('page');
        $page == '' && $page = 1;
        $pageSize = I('pageSize');
        $pageSize == '' && $pageSize = 20;
        $map['status'] = DBCont::NORMAL_STATUS;
        $map['city'] = $city;
        
        if ($search){
            $map['name'] = ['like', "%{$search}%"];
        }

        $library_name = D('Library')->where($map)->field(['id, name'])->page($page, $pageSize)->select();
        $total_count = D('Library')->getListforCount($map);
        
        $lib_data = [];
        foreach ($library_name as $k => $v){
            $lib_data[$k]['id'] = $v['id'];
            $lib_data[$k]['text'] = $v['name'];
        }
        $data = [
            'total_count' => $total_count,
            'data' => $lib_data,
        ];

        $this->ajaxReturn($data);
    }
            
    // js使用代码    
    var city = $('input[name=city]').val();
    var query = {
        city:city,
        pageSize: "{:C('ADMIN_PER_PAGE_NUM')}"
    };
    
    select2_ajax($('.select-two'), "{:U('getLibrary')}", query);
```

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
    
        //exportExcelByXlsx为 Qscmf\Builder\ExportExcelByXlsx trait提供的方法
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

筛选导出列
```php
//列配置，default为true表示默认选中状态, required为true表示必选
$cols_options = [
    [
        'key' => 'name',
        'title' => '商家名称',
        'default' => true,
        'required' => true
    ],
    [
        'key' => 'account',
        'title' => '账号',
        'default' => true
    ],
    [
        'key' => 'address',
        'title' => '商家地址'
    ],
    [
        'key' => 'num',
        'title' => '核销次数'
    ],
    [
        'key' => 'status',
        'title' => '状态'
    ],
    [
        'key' => 'explain',
        'title' => '优惠券使用说明'
    ]
];

//将列配置复制给第二个参数的键值 export_cols
//控件会将选择的列数据post至url,可通过I('post.exportCol')获取，再进行数据筛选逻辑处理。
$builder->addTopButton('export', array('export_cols' => $cols_options, 'title' => '样例导出', 'data-url' => U('/admin/post/export'), 'data-filename' => '文章列表', 'data-streamrownum' => '10'));


```

导出数据为多张工作表  
```php
 $export_arr = [
    ['sheetName' => 'Sheet1', 'url' => U('/admin/post/export'), 'rownum' => '15'],
    ['sheetName' => 'Sheet2', 'url' => U('/admin/post/export'), 'rownum' => '15'],
 ];
 
 $builder->addTopButton('export', ['data-url' => json_encode($export_arr), 'data-filename' => '导出列表', 'data-streamrownum' => intval($export_arr[0]['rownum'])]);
```
业务层错误提示
```php
可在导出数据处理的action位置进行错误验证，使用$this->errro("test") 抛出错误
插件可自动获取错误信息并alert提示用户
```

### 文件批量导出并打包（zip）
使用样例
```php
.
.
.
class PostController extends GyListController{
.
.
.
    
        $builder = new \Common\Builder\ListBuilder();
        //第一个参数指定download类型，第二个参数是指定需要覆盖的html组件属性
        /*属性值如下
                必填：data-url   为点击导出按钮后ajax请求的地址
                选填：
                    data-filename  批量导出压缩包文件名
                    title           按钮名称
                    data-select     值为bool类型，判断是否勾选，默认true，即默认开启
                    data-tips       承接data-select属性，如果开启，在未勾选内容情况下提示的信息
         */
        $builder->addTopButton('download', array('data-url' => U('download')));
    
.
.
.
    
    /*
    导出下载链接请求的action
    ajax返回json数据格式如下：
        {
            "count": "5",
            "pageSize": 2,
            "list": [
                {
                    "id": "1",
                    "name": "下载重命名的文件名",
                    "url": "https://media.t4tstudio.com/TJlJL2wlKB4Ezb5_qQrp0okWb2c=/Fv2T8J6s6Pupj6zbs2xvdMf9GKN2",
                    "suffix": "mp3"
                },
                ....
            ]
        }
        返回值注解（下面所有键名必填）：
        count   总记录数
        pageSize 单页最大记录数    注意：如果是下载单页的数据，令count<=pageSize即可
        list    下载的数据列表
            id  数据的id
            name  重命名的文件名   注意：请遵守操作系统文件命名规范
            url   下载链接地址
            suffix 文件后缀名
    */
    public function download(){
        //$page 为页码，若不需要请忽略该值
        $page = I('page',1);
        $count = M('Test')->count();
        $pageSize = C('ADMIN_PER_PAGE_NUM', null, false);
        $data = M('Test')->page($page,$pageSize)->select();
        $return_data = [
            'count' => $count,
            'pageSize' => $pageSize,
            'list'=>$data,
        ];
        $this->ajaxReturn($return_data);
    }

```

## Formbuilder

#### 事件
+ startHandlePostData   
确定按钮会监听该事件类型，可传递一个按钮描述。触发该事件后确定按钮会无效，描述会改成传递的字符串。
+ endHandlePostData  
确定按钮会监听该事件类型，触发该事件，确定按钮会重新生效，按钮描述会恢复。

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

勾子：qiniu_notify  
```php
跟根据业务需要，自定义该勾子的行为，传递参数为数组，数组包含file_id 和 duration。
```


#### ueditor
指定上传文件的url格式采用包含域名的url格式（默认采用相对url路径）
```php
//addFormItem第七个参数，传递指定的上传处理地址，加上urldomain参数，设为1
->addFormItem('desc', 'ueditor', '商家简介', '', '', '', 'data-url="/Public/libs/ueditor/php/controller.php?urldomain=1"')
```

使用oss作为文件存储服务
```php
//addFormItem第七个参数，传递指定的上传处理地址, oss设为1表示开始oss上传处理，type为指定的上传配置类型
->addFormItem('content', 'ueditor', '正文内容','', '','','data-url="/Public/libs/ueditor/php/controller.php?oss=1&type=image"')
```

复制外链文章时，强制要求抓取外链图片至本地，未抓取完会显示loadding图片(默认也会抓取外联图片，但如果未等全部抓取完就保存，此时图片还是外链)
```php
//addFormItem第七个参数，设置data-forcecatchremote="true"
->addFormItem('desc', 'ueditor', '商家简介', '', '', '', 'data-forcecatchremote="true"')
```

设置ue的option参数
```php
//如：想通过form.options来配置ue的toolbars参数
//组件会自动完成php数组--》js json对象的转换，并传入ue中
->addFormItem('content', 'ueditor', '内容', '', ['toolbars' => [['attachment']]])
```

自定义上传config设置

```blade
在app/Common/Conf 下新增ueditor_config.json或者ueditor_config.php(返回数组)，该文件将会替换掉默认的config.json。如有客制化config.json的需求，定制该文件即可。
```

## CompareBuilder
实现数据对比简化
#### 代码示例
```php
    $builder = new CompareBuilder();
        $old=[
            'title'=>'123',
            'no_change'=>'aaa',
            'html'=>'<h1>456</h1><p>123</p>'
        ];
        $new=[
            'title'=>'456',
            'no_change'=>'aaa',
            'html'=>'<h1>123</h1><p>456</p>'
        ];
        $builder->setData($old,$new)
            ->addCompareItem('title',CompareBuilder::ITEM_TYPE_TEXT,'标题')
            ->addCompareItem('no_change',CompareBuilder::ITEM_TYPE_TEXT,'没有变化')
            ->addCompareItem('html',CompareBuilder::ITEM_TYPE_HTMLDIFF,'html对比')
            ->display();
```
#### 截图
![image](https://user-images.githubusercontent.com/13673962/65034234-34b41c80-d979-11e9-8be6-6a50c546a9c2.png)


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
$module 需要高亮左侧菜单的module_nam，默认为MODULE_NAME
$controller 需要高亮左侧菜单的controller_name，默认为CONTROLLER_NAME
$action 需要高亮左侧菜单的action_name，默认为index
```

#### setTopHtml
```blade
该方法用于设置页面顶部自定义html代码

参数
$top_html 顶部自定义html代码
```
截图  
ListBuilder，如图所示

![image](https://user-images.githubusercontent.com/35066497/69775189-fd60b800-11d2-11ea-9438-1a1d3dc9190b.png)

FormBuilder，如图所示

![image](https://user-images.githubusercontent.com/35066497/69775187-fb96f480-11d2-11ea-8447-438dd1585982.png)

CompareBuilder，如图所示

![image](https://user-images.githubusercontent.com/35066497/69775169-ea4de800-11d2-11ea-8a5e-60f6a1f7e792.png)

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

## 重置RBAC用户表和用户与用户组关联表
#### 用法
+ config.php配置INJECT_RBAC  
```blade
// key为标识字段，用户登录后存入session中
// user为RBAC对应的用户数据表（程序使用D函数）
// role_user为用户与用户组关联数据表（程序使用原生sql）
'INJECT_RBAC' => [
    ['key' => 'PUBLIC_USER_LOGIN_ID', 'user' => 'User', 'role_user' => 'qs_role_user']
]
```

+ 用户登录后使用cleanRbacKey清空key的session值再重置对应key的session值

+ 用户登出后使用cleanRbacKey清空key的session值

## 权限过滤机制
不同用户一般只能看到与自己相关的数据，该机制可以限制后台用户访问数据的权限，不用针对不同用户分别处理where表达式，降低开发难度。

#### 用法
+ 用户登录成功后设置AUTH_RULE_ID的session值；

+ 配置对应Model类的$_auth_ref_rule（若查询数据表存在别名，自动处理auth_ref_key为“别名.字段名”）。

如某机构（全思小伙伴）只能查看其创建书库点的书箱，则：
```php
    // auth_ref_key是与用户关联的字段，即AUTH_RULE_ID的值
    // ref_path是该字段有关的数据表及其对应字段
    
    // 机构OrganizationModel的配置
    protected $_auth_ref_rule = array(
        'auth_ref_key' => 'id',
        'ref_path' => 'Organization.id'
    );
    
    // 书库点LibraryModel的配置
    protected $_auth_ref_rule = array(
        'auth_ref_key' => 'org_id',
        'ref_path' => 'Organization.id'
    );
    
    // 书箱BoxModel的配置
    protected $_auth_ref_rule = array(
        'auth_ref_key' => 'library_id',
        'ref_path' => 'Library.id'
    );
```

若不使用该机制，则需要根据登录用户来处理where表达式，会导致查询的层级越高，代码量就越多：
```php
    // 不使用该机制，机构查询书箱数据，则需要根据登录用户获取org_id，再根据org_id获取library_id，再根据library_id获取书箱id，最后根据书箱id找出书箱数据：
    if (session('?' . C('USER_AUTH_KEY')){
        $org_id = D('OrganizationUser')->where(['id' => session(C('USER_AUTH_KEY'))])->getField('org_id');
        !$org_id && $org_map['_string'] = "1=0";
        $org_id && $library_ids = D('Library')->where(['org_id' => $org_id])->getField('id', true);
        if ($library_ids){
            $box_ids = D('Box')->where(['library_id'=>['IN',$library_ids]]) -> getField('id',true);
            $box_ids && $org_map['id'] = ['IN', $box_ids];
            !$box_ids && $org_map['_string'] = "1=0";
        }else{
            $org_map['_string'] = "1=0";
        }
        $org_map && $map = array_merge($map, $org_map);
    }
    D('Box')->where($map)->select();
    
```

使用该机制后，机构查询书箱数据：
```php
    D('Box')->where($map)->select();
```

## 扩展权限过滤机制
如系统存在机构用户OrgUser与书库点管理员LibraryUser，有书库点Library数据分别与他们关联（org_id与id）时，对应的LibraryModel应该这样配置：
```php
// 对于OrgUser，书库点LibraryModel的配置
protected $_auth_ref_rule = array(
    'auth_ref_key' => 'org_id',
    'ref_path' => 'Organization.id'
);

// 对于LibraryUser，书库点LibraryModel的配置
protected $_auth_ref_rule = array(
    'auth_ref_key' => 'id',
    'ref_path' => 'Library.id'
);
```

显然之前的权限过滤机制不能满足需求。

当系统存在多种不同类型的用户，而这些用户与数据相关联的字段不一致，扩展后可以根据不同类型的用户配置不同的权限过滤。

#### 用法
+ 配置对应Model类的$_auth_ref_rule，自定义不同用户类型的权限过滤

```php
    // 机构OrganizationModel的配置
    protected $_auth_ref_rule = array(
        'auth_ref_key' => 'id',
        'ref_path' => 'Organization.id'
    );
    
    // 用户类型org为机构
    // 用户类型library为书库点管理员

    // 书库点LibraryModel的配置
    protected $_auth_ref_rule = array(
        'org' => [
            'auth_ref_key' => 'org_id',
            'ref_path' => 'Organization.id'
        ],
        'library' => [
            'auth_ref_key' => 'id',
            'ref_path' => 'Library.id'
        ]
    );
```

+ 登录方法需先清空再设置“AUTH_RULE_ID”、“AUTH_ROLE_TYPE”对应的值

```php

    // 用户类型为“library”的登录方法
    public function libraryUserLogin($name,$pwd){
        // 省略登录逻辑处理
        ……
        ……
        ……

        // 登录成功后
        if ($r !== false){
            cleanRbacKey();
                   
            session('LIBRARY_USER_LOGIN_ID',$ent['id']);            
            session(C('USER_AUTH_KEY'), $ent['id']);
            
            \Qscmf\Core\AuthChain::setAuthFilterKey($ent['library_id'], 'library');

            session('ADMIN_LOGIN', true);
            session('HOME_LOGIN', null);
            sysLogs('书库点管理员后台登录');

            return true;
        }else{
            return false;
        }
    }
    
    // 用户类型为“org”的登录方法
    public function adminLogin($user_name, $pwd){
        // 省略登录逻辑处理
        ……
        ……
        ……
        
        // 登录成功后            
        if (!$ent){
            return false;
        }             
        cleanRbacKey();

        // 设置超级管理员权限
        if ($ent['id'] == C('USER_AUTH_ADMINID')) {
            session(C('ADMIN_AUTH_KEY'), true);
        } else {
            session(C('ADMIN_AUTH_KEY'), false);
        }        
        
        session('ORG_USER_LOGIN_ID', $ent['id']);      
        session(C('USER_AUTH_KEY'), $ent['id']);
        
        \Qscmf\Core\AuthChain::setAuthFilterKey($ent['company_id'], 'org');
        
        session('ADMIN_LOGIN', true);
        session('HOME_LOGIN', null);
        sysLogs($ent['company_id'] ? '机构后台登录' : '平台用户后台登录');

        return true;
    }
    
```
+ 登出方法需要使用函数“cleanRbacKey”、“cleanAuthFilterKey”清空对应的值

```php
    // 用户类型为“org”的登出方法
    public function sso_out(){
        if (isAdminLogin()) {
            cleanRbacKey();
            
            \Qscmf\Core\AuthChain::cleanAuthFilterKey();

            session(C('ADMIN_AUTH_KEY'), null);
            session(C('USER_AUTH_KEY'), null);
            session('ADMIN_LOGIN', null);
            sysLogs('后台登出');
        }
    }
    
    // 用户类型为“library”的登出方法
    public function libraryUserLogout(){
        if (isAdminLogin()) {
            cleanRbacKey();
            
            \Qscmf\Core\AuthChain::cleanAuthFilterKey();

            session(C('ADMIN_AUTH_KEY'), null);
            session(C('USER_AUTH_KEY'), null);
            session('ADMIN_LOGIN', null);
            sysLogs('后台登出');
        }
        
        $this->redirect('Public/libraryUserLogin');
    }
```

## 工具类

#### RedisLock类：基于Redis改造的悲观锁
+ 先获取锁再执行业务逻辑，执行结束释放锁。
+ 保证同一个方法的并发重复操作请求只有一个请求可以获取锁，在不进行高延迟事务处理的场景下可以使用。

##### lock
```blade
该方法可以获取锁

参数 
$key 名称
$expire 过期时间 单位为秒

返回值
锁成功返回true 锁失败返回false
```

##### unlock
```blade
该方法可以释放锁

参数 
$key 名称

返回值
释放锁的个数
```
##### 代码示例
```php
public function execShell(){
    $redis_lock = \Qscmf\Lib\RedisLock::getInstance();
    $is_lock = $redis_lock->lock('exec_shell_lock_key', 60);
    $is_lock === false && $this->error('请一分钟后再操作');

    shell_exec('ll >/dev/null');
    
    $redis_lock->unlock('exec_shell_lock_key');
}
```
## 全局函数

#### base64_url_encode
对url进行base64转码，转码前会先对+ /，进行处理

#### base64_url_decode
将base64转回url，与base64_url_encode搭配使用

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

#### cleanRbacKey
清空INJECT_RBAC标识key的session值

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

ROOT 指定子目录，默认为空

SITE_URL 包含子目录的网站根地址

HTTP_PROTOCOL  返回http或者https协议字符串

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


#### 压缩前端js代码
压缩办法很多，这里提供一种配置简单的方式，[传送门](https://gist.github.com/gaearon/42a2ffa41b8319948f9be4076286e1f3)

## 文档
由于工作量大，文档会逐步补全。

## lincense
[MIT License](https://github.com/tiderjian/lara-for-tp/blob/master/LICENSE.MIT) AND [996ICU License](https://github.com/tiderjian/lara-for-tp/blob/master/LICENSE.996ICU)
