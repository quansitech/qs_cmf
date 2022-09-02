```php
1. (升级至composer版本)修改原来buider类的命名空间, 修改Common/Conf/config.php里的相关配置 
     检查根目录与www目录下的文件差异，升级至最新文件
     从最新代码移植lara文件夹到根目录
     升级Common/Common/function.php
2. (升级至v2.0.0版本)修改AppInitBehavior 队列相关类的命名空间，修改Common/Conf/config.php 跟队列有关的设置，从env读取
    修改Common/Model/QueueModel类的队列命名空间，升级后注意计划任务相关的键值前缀，旧版队列的前缀会和新版队列的前缀会不一样，新版计划任务前缀会根据设置的值而定，旧版则不受设定前缀影响
    修改app/resque文件
    检查queue数据表，是否缺少schedule字段，添加schedule表
3. (升级至v2.0.0版本)如该项目之前有使用ElasticsearchController, 修改Home/Controller/ElasticsearchController的Elasticsearch类的命名空间，修改Model里跟Elasticsearch相关的命名空间
    修改app/makeIndex.php文件 
4. (升级至v2.0.1版本) 修改Admin/Controller/QueueController、Behaviors/AppInitBehavior、Common/Model/QueueModel、用到Job状态的DBCont的命名空间
    删除Gy_Library/DBcont与Job状态有关的代码
5. (升级至v2.0.5版本)修改CateHelperTrait和ContentHelperTrait的命名空间,移除Gy_library里的文件
    修改CusUpload的命名空间,移除Gy_library里的文件
    修改GyController和GyListController，分别继承QsController和QsListController，删除同名方法
    修改GyModel和GyListModel，分别继承QsModel和QsListModel，删除同名方法
    删掉Common\function.php里的genSelectByTree、isAdminLogin、list_to_tree函数
    删除Common\Lib 的Flash和FlashError
    修改GyRbac的命名空间,更名为QsRbac
    GyPage更名为QsPage,原GyPage继承QsPage,删除Common/Conf/config 里的 VAR_PAGE
6.(升级到v3.0.0以上版本)检查有无使用队列的计划任务功能，如使用了，可用升级脚本进行升级
    项目根目录/www# php index.php Qscmf/UpgradeFix/v300FixSchedule/queue/[计划队列名] maintenance
   如用到七牛云的音视频上传功能，注意qs_file_pic的字段有无缺失，对比qs_cmf的迁移文件里的qs_file_pic表的定义
    修改app/resque文件 修改绑定模块，将Home改为绑定到Qscmf
7.(升级到v4.0.0以上版本)检查app/Behaviors/AppInitBehavior.class.php有无DOMAIN、SITE_URL、HTTP_PROTOCOL的常量定义，如有，将代码删除即可
8.(升级到v5.0.0以上版本)检查有无引用Org\Util\String类，如有，将命名空间改为Org\Util\StringHelper，并将类名String修改为StringHelper。
9.(升级到v6.0.0以上版本)检查有无在www/Public/views/Admin/common/common.js和www/Public/views/common.css中自定义客制化代码，如有，请
   将代码迁移处理。上述两文件将移植核心库。并在app/Admin/View/default/common/dashboard_layout.html中将以上两文件的引用分别指向
   __PUBLIC__/libs/admin/common.js和__PUBLIC__/libs/admin/common.css。
10.(升级到v7.0.0以上版本)7.0版本移除了位于Qscmf/Lib下的QsExcel代码，检查有无使用该类，如果使用了，请安装使用https://github.com/quansitech/qs-excel。
11.(升级到v8.0.0以上版本)8.0提供了许多扩展机制，部分原来继承在框架里的功能都移到了独立的composer扩展。
    因此升级到该版本，必须注意业务系统有无使用了已经移除的组件功能，如果有，则需要查看组件的安装方法，安装后才会不影响原有的功能
    移除的组件有以下内容
    （1）formbuilder formitem的vedio_vod类型 (https://github.com/quansitech/qscmf-formitem-vod)
     (2) formbuilder formitem的qiniu_video和qiniu_audio类型 (https://github.com/quansitech/qscmf-formitem-qiniu)
     (3) listbuilder topbutton的 download类型 (https://github.com/quansitech/qscmf-topbutton-download)
     (4) listbuilder topbutton的 export类型 (https://github.com/quansitech/qscmf-topbutton-export)
     (5) formbuilder formitem的 audio_oss、audios_oss、file_oss、files_oss、picture_oss、pictures_oss、picture_oss_intercept、pictures_oss_intercept (https://github.com/quansitech/qscmf-formitem-aliyun-oss)

    在项目的composer.json文件的scripts设置项修改为
    "scripts": {
            "post-root-package-install": [
                "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
            ],
            "post-autoload-dump": [
                "./vendor/bin/qsinstall",
                "./vendor/bin/qsautoload",
                "@php artisan package:discover --ansi",
                "@php artisan qscmf:discover --ansi",
                "@php ./www/index.php /qscmf/createSymlink"
            ]
        }

    删除app/Behaviors文件架下的InitHookBehavior.class.php、LoadDBConfigBehavior.class.php
    删除app/Common/Conf/tags.php 中 InitHook 和 LoadDBConfig的设置

    检查根目录下的tp.php文件，有无LARA_DIR 和 ROOT_PATH的常量定义，没有则添加
    defined('LARA_DIR') || define('LARA_DIR', __DIR__  .  '/lara');
    defined('ROOT_PATH') || define('ROOT_PATH', __DIR__);

    检查composer.json文件，并添加以下内容
    "require-dev": {
        "phpunit/phpunit": "^8.0",
        "laravel/dusk": "^5.0",
        "mockery/mockery": "^1.2",
        "fzaninotto/faker": "^1.4"
    },
    "autoload-dev": {
        "psr-4": {
            "Lara\\Tests\\":"lara\/tests"
        }
    },

    如果是采用swoole-webhook的部署方式，拉取下最新的镜像

12.(升级到v9.0.0以上版本)
   ----------------------------------
   defined('LARA_DIR') || define('LARA_DIR', __DIR__  .  '/lara');
   \Bootstrap\Context::providerRegister(true);
   \Larafortp\ArtisanHack::init($app);
   ----------------------------------
   1. 将上面的代码复制到根目录下的artisan |   $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);   |  前

   2. 检查 app/Admin/View/default/common/head.html 中的 <div class="navbar-left"> 元素前面有没包裹 <div class="navbar-container"> ，没有则加上

   在app/Admin/View/default/common/dashboard_layout.html 加入
   <link href="__PUBLIC__/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" type="text/css" />
   <script src="__PUBLIC__/libs/perfect-scrollbar/perfect-scrollbar.min.js" type="text/javascript"></script>



   3. 删除tp.php 里内容，替换成以下的
   --------------------------------
   <?php
   // 应用入口文件
   ini_set('display_errors', '0');

   if(!function_exists('show_bug')){
       function show_bug($object){
           echo "<pre style='color:red'>";
           var_dump($object);
           echo "</pre>";
       }
   }

   //require __DIR__ . '/vendor/tiderjian/think-core/src/Common/functions.php';
   //require __DIR__ . '/app/Common/Common/function.php';
   require_once __DIR__ . '/vendor/autoload.php';

   $dotenv = \Dotenv\Dotenv::create(__DIR__ );
   $dotenv->load();

   // 引入ThinkPHP入口文件
   require 'vendor/tiderjian/think-core/src/ThinkPHP.php';
   -----------------------------------

   4. 检查composer.json的scripts, 将"post-root-package-install" 里的执行脚本移到"post-autoload-dump"第一行，如下
   -----------------------------------
   "scripts": {
           "post-autoload-dump": [
               "@php -r \"file_exists('.env') || copy('.env.example', '.env');\"",
               "./vendor/bin/qsinstall",
               "./vendor/bin/qsautoload",
               "@php artisan package:discover --ansi",
               "@php artisan qscmf:discover --ansi",
               "php ./www/index.php /qscmf/CreateSymlink"
           ]
       }
   -----------------------------------


13.(升级到v10.0.0以上版本)(该版本仅做升级过渡，勿使用，命令行运行模式存在重大缺陷)
  全局搜索有无使用ListBuilder->alterTableData 方法，如果有，则将里面的变量占位符{$字段名} 改为 __字段名__

  lara/server.php 文件，找到$uri的定义，在其后面加上以下代码
  ----------------------------------
    $_SERVER['DUSK_TEST'] = true;
  ----------------------------------

14.(升级到v11以上版本)
  在v10版本先完成以下操作
  数据库执行sql
  --------------------------------
  alter table migrations add column `after` tinyint(1) not null default 0 after migration
  alter table migrations add column `run` tinyint(1) not null default 0 after migration
  alter table migrations add column `before` tinyint(1) not null default 0 after migration

  update migrations set `after`=1,`run`=1,`before`=1
  --------------------------------
```

v11->v12升级步骤，[点击查看](https://github.com/quansitech/qs_cmf/blob/master/docs/UpgradeTo12.md)



##### v13修改计划

- [ ] CompareBuilder FormBuilder ListBuilder 删除display方法
- [ ] 重构ButtonType save的保存提交算法 原因是SubTableBuilder也可能会采用该方法来设置 column 的class，当同时作为listBuilder的modal使用时，就会被错误的一并save提交先采用全局变量来开发重置能力，但全局变量容易存在冲突，并不是一种好的解决方案，仅作过渡使用，合理的做法应该让ListBuilder或者SubTableBuilder来决定Column 的TargetForm，避免互相影响。
- [ ] showFileUrl不再处理除本地文件存储外的功能，例如oss的处理