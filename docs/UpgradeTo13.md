## v13升级步骤

+ 若 composer.json 文件有 require-dev，则修改
  + 从v13版本的think-core composer.json文件中找到require-dev的内容，替换掉脚手架的
    ```php
    "phpunit/phpunit": "^8.0",
    "laravel/dusk": "^5.0",
    "mockery/mockery": "^1.2",
    "fzaninotto/faker": "^1.4"
  
    // 改为
    "phpunit/phpunit": "^9.3.0",
    "laravel/dusk": "^6.9.0",
    "mockery/mockery": "^1.2",
    "fakerphp/faker": "^1.10.0"
    ```

+ 修改 *env* 加载方式
  ```php
  // 查找项目中 env 的创建代码。（全局搜索Dotenv::create，排除vendor目录）
  \Dotenv\Dotenv::create
  
  // 替换成，默认为不可以改变env的值
  \Dotenv\Dotenv::createImmutable
  
  // 如修改 app/tp.php 和 app/resque.php 文件中
  $dotenv = \Dotenv\Dotenv::create(__DIR__ );
  
  // 改为
  $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ );
  ```

+ 引入 富文本组件 扩展包
    ```php
    composer require quansitech/qscmf-formitem-ueditor
    ```

+ 检查使用了富文本组件的页面，将 Public/libs/ueditor/ 替换为 Public/ueditor/，用于更新 css、js、入口文件等资源的路径。
  ```php
  // 更新前
  ->addFormItem("oss_ueditor", "ueditor","oss_ueditor",'', '','','data-url="/Public/libs/ueditor/php/controller.php?os=1&type=ueditor&vendor_type=aliyun_oss"')
  
  // 更新后
  ->addFormItem("oss_ueditor", "ueditor","oss_ueditor",'', '','','data-url="/Public/ueditor/php/controller.php?os=1&type=ueditor&vendor_type=aliyun_oss"')  
  ```
  ```html
  // 更新前
  <script type="text/javascript" charset="utf-8" src="{:asset('libs/ueditor/ueditor.config.js')}"></script>
  
  // 更新后
  <script type="text/javascript" charset="utf-8" src="{:asset('ueditor/ueditor.config.js')}"></script>
  ```
  
  系统配置中也会有调用富文本的设置，需要检查有无设置配置项，如果有，data-url也需要做以上修改
  
+ CompareBuilder FormBuilder ListBuilder 废弃display方法，使用build方法替换

+ 若实现了 showFileTitle 公共函数，需删除
 
+ 检查是否使用了 jquery 新版 3.7 已删除方法
 
+ 其他说明
  + 移除了 *Common\Util\Mail* 发送邮箱的工具
  + 移除了 *Common\Util\PDF* 
  + 移除了 *Gy_Library\DES*

+ 复制v13迁移文件 2023_11_21_015722_add_hashid_to_qs_file_pic.php 到 项目的迁移目录，执行迁移

+ 执行数据库迁移

+ 重构了脚手架的api/upload接口，接口的请求参数格式都会有变化，新增了hash_id的去重功能，同时v13 think-core 的 file picture组件也会根据新接口做重
构。复制v13的qscmf/app/Api/Controller/UploadController.php 覆盖本项目的对应文件,检查项目有无进行上传接口的客制化改造，如果有则需要对新的上传代码做同样的客制化处理。
[新upload API说明文档](https://github.com/quansitech/qs_cmf/blob/master/docs/Upload.md)

+ think-core删除"guzzlehttp/guzzle": "^6.3" 依赖，如果项目依赖了guzzle，需要自行处理


#### 使用php8.2的修改
+ 将 DateTime::ISO8601 替换成 DateTimeInterface::ATOM

**移除函数其它解决方案：已理解原函数的输入输出值后创建一个同名全局函数。**

#### 需要注意使用php8.2不兼容的变更

+ 检查继承了父类/实现了接口类对应方法的返回类型，改为一致

+ 将phpstorm的php版本设置成8.2, 开启View--Tool Windows--Problems--Inspect Code 分析业务代码是否存在潜在语法错误

#### think-core的修改
  + 删除了下列文件
    + asset\libs\jquery\flot\*
    + asset\libs\jquery\inputmask\*
    + asset\libs\jquery\jquery.ba-resize.min.js
    + asset\libs\jquery\jquery.dataTables.js
    + asset\libs\jquery\jquery.knob.js
    + asset\libs\jquery\jquery.placeholder.js
    + asset\libs\jquery\jquery.random-password.js
    + asset\libs\jquery\jquery.sparkline.js
    + asset\libs\jquery.sparkline.min.js
    + asset\libs\donatecart.js
    + asset\libs\jquery.form.js
    + asset\libs\jquery.form.min.js
    + asset\libs\jquery.min.js
    + asset\libs\jquery.printPage.js
    + asset\libs\jquery.stickytableheaders.min.js
    + asset\libs\validate\additional-methods.min.js
    + asset\libs\validate\jquery.validate.min.js
    + asset\libs\jquery.validate.min.js
    + asset\libs\jquery.waypoints.min.js
    + asset\libs\modernizr.js
    + asset\libs\tooltip.js
    + asset\libs\AdminLTE\AdminLTE.js
    + asset\libs\superslide.js
    + asset\libs\amaze\js\amazeui.js
    + asset\libs\amaze\js\amazeui.min.js
    + asset\libs\amaze\js\handlebars.min.js
    + asset\libs\amaze\js\jquery.min.js
    + asset\libs\audioplayer\*
    + asset\libs\label-select\lib\jquery.js
    + asset\libs\morris\*
    + asset\libs\videojs\*
    + asset\libs\select2\select2.full.js
    + asset\libs\select2\select2.min.js
    + asset\libs\select2\select2.min.css
    + asset\libs\ckeditor\*
    + asset\libs\masonry\*
    + asset\libs\fullcalendar\*
    + asset\libs\qsuploader.boundle.js
    + asset\libs\backbone\*
    + asset\libs\bootstrap-typeahead\*
    + asset\libs\bootstrap-wysihtml5\*
    + asset\libs\bootstrap3-editable\*
    + asset\libs\dataTables.bootstrap\*
    + asset\libs\fancybox
    + asset\libs\highcharts
    + asset\libs\Fixed-Header-Table-master
    + asset\libs\flexslider
    + asset\libs\jquery-ui
    + asset\libs\jquery-ui-1.11.4
    + asset\libs\jvectormap
    + asset\libs\magnific-popup
    + asset\libs\messenger
    + asset\libs\picshow\pinchzoom.js
    + asset\libs\slimScroll\jquery.slimscroll.js
    + asset\libs\scrollup
    + asset\libs\umeditor
    + asset\libs\tmall-cart
    + asset\libs\polyfill
    + asset\libs\video.min.js
    + asset\libs\videojs-ie8.min.js
    + asset\libs\modal.js
    + asset\libs\stickUp.js

  + 修改了下列文件
    + 修改asset\libs\label-select\label-select.js语法兼容，并编译了一下
    + 升级asset\libs\select2\css\* 从原来的Select2 4.0.6-rc.1 到现在的 Select2 4.1.0-rc.0
    + 升级asset\libs\select2\js\* 从原来的Select2 4.0.6-rc.1 到现在的 Select2 4.1.0-rc.0
    + 增加asset/libs/jquery-extend/jquery.extend.js
    + 更新版本asset\libs\bootstrap-datepicker\* 从1.4.0版本更新到最新的1.10.0
    + 修改asset\libs\cropper\main.js，兼容jquery最新语法
    + 修改asset\libs\cui\cui.extend.min.js， 兼容jquery最新语法
    + 修改src\Library\Qscmf\Builder\FormType\File\file.html			----------------开始------------------
    + 修改src\Library\Qscmf\Builder\FormType\Files\files.html
    + 修改src\Library\Qscmf\Builder\FormType\Picture\picture.html		$.parseJson改成JSON.parse
    + 修改src\Library\Qscmf\Builder\FormType\Pictures\pictures.html  	-------------结束---------------------
    + 修改src\Library\Qscmf\Builder\FormType\Citys\citys.html		----------unbind改成off---------
    + 修改src\Library\Qscmf\Builder\FormType\Districts\districts.html	----------unbind改成off---------
    + 修改asset\libs\cropper\main.js 替换该文件中的.selector属性（jquery3.0版本已经移除 ）


#### qs_cmf 所做的修改
  + 升级所有的jquery文件版本为3.7.1
  + app/Home/View/default/Index/test.html  （asset\libs\qsuploader.boundle.js 已删除，注释掉相关使用）
	+ app/Admin/View/default/common/dashboard_layout.html （将自定义扩展的$.isWindow()的文件通过script引入到其中 用来适配当前的bootstrap版本）
	+ app/Admin/View/default/common/layout.html （将自定义扩展的$.isWindow()的文件通过script引入到其中 用来适配当前的bootstrap版本）
	+ app\Admin\View\default\common\dashboard_layout.html 删除asset\libs\messenger的相关引入
  + 删除www/public/addons/Qiniu
  + 修改www/public/static/common.js
  + 修改www/public/views/home/app.js
	+ 修改www/public/views/home/pc/js/app.js