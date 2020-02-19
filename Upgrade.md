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
    GyController和GyListController更名为QsController和QsListController，修改GyController和GyListController，分别继承QsController和QsListController
    GyModel和GyListModel更名为QsModel和QsListModel，修改GyModel和GyListController，分别继承QsModel和QsListModel
    删掉Common\function.php里的genSelectByTree、isAdminLogin、list_to_tree函数
    删除Common\Lib 的Flash和FlashError
    修改GyRbac的命名空间,更名为QsRbac
    GyPage更名为QsPage,原GyPage继承QsPage,删除Common/Conf/config 里的 VAR_PAGE
6.(升级到v3.0.0以上版本)检查有无使用队列的计划任务功能，如使用了，可用升级脚本进行升级
    项目根目录/www# php index.php Qscmf/UpgradeFix/v300FixSchedule/queue/[计划队列名] maintenance
   如用到七牛云的音视频上传功能，注意qs_file_pic的字段有无缺失，对比qs_cmf的迁移文件里的qs_file_pic表的定义
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

    在项目的composer.json文件的scripts设置项添加
    "post-root-package-install": [
        "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
    ],
    "post-autoload-dump": [
        "./vendor/bin/qsautoload",
        "@php artisan package:discover --ansi",
        "@php artisan qscmf:discover --ansi",
        "@php ./www/index.php /qscmf/createSymlink"
    ]
    
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
```
