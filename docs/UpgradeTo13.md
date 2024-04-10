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
  // 查找项目中 env 的创建代码。（全局搜索，排除vendor目录）
  \Dotenv\Dotenv::create
  
  // 替换成，默认为不可以改变env的值
  \Dotenv\Dotenv::createImmutable
  
  // 如修改 /tp.php 文件中
  $dotenv = \Dotenv\Dotenv::create(__DIR__ );
  
  // 改为
  $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ );
  
  // 如修改 app/resque.php 文件中
  $dotenv = \Dotenv\Dotenv::create(__DIR__ . '/..' );
  
  // 改为
  $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..' );
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

+ showFileTitle 公共函数移到了核心包，若项目也实现了同名的公共函数，需检查是否有客制化代码，有则修改函数名并替换，没有则删除。
 
+ 其他说明
  + 移除了 *Common\Util\Mail* 发送邮箱的工具
  + 移除了 *Common\Util\PDF* 
  + 移除了 *Gy_Library\DES*

+ 复制v13迁移文件 2023_11_21_015722_add_hashid_to_qs_file_pic.php 到 项目的迁移目录，执行迁移

+ 执行数据库迁移

+ 重构了脚手架的api/upload接口，接口的请求参数格式都会有变化，新增了hash_id的去重功能，同时v13 think-core 的 file picture组件也会根据新接口做重
构。需要检查后台上传组件如果是使用了自定义data-url的方式，必须将url地址改成新的api地址格式。复制v13的qscmf/app/Api/Controller/UploadController.php 覆盖本项目的对应文件,检查项目有无进行上传接口的客制化改造，如果有则需要对新的上传代码做同样的客制化处理。
[新upload API说明文档](https://github.com/quansitech/qs_cmf/blob/master/docs/Upload.md)
  
   ps: 部分旧项目qs_file_pic表可能缺少了mime_type字段，需要手动补充回该字段，复制qs_cmf脚手架里的2022_07_18_014941_alter_file_pic_add_mime_type.php迁移文件到项目的迁移目录，执行迁移

+ think-core删除"guzzlehttp/guzzle": "^6.3" 依赖，如果项目依赖了guzzle，需要自行处理

+ 若使用了以下扩展包，请限制最低版本
  ```php
  "quansitech/qscmf-topbutton-export":"^2.0",
  "quansitech/qscmf-topbutton-download":"^2.0",
  "quansitech/qscmf-buttontype-modal":"^3.0",
  
  "quansitech/qscmf-formitem-object-storage":"^2.0.0",
  "quansitech/easy-get-flower":"^1.0.3",
  ```
  
+ 8.2后从数据库获取的值会根据字段类型返回，如varchar类型会返回string, int类型会返回int类型。而8.0则清一色返回string。因此需要检查用了"===" "!=="的代码。
如果是前后端分离的项目，则需要检查select类组件，有无因接口返回的值类型改变而导致代码失效。


#### 使用php8.2的修改
+ 将 DateTime::ISO8601 替换成 DateTimeInterface::ATOM
+ 获取数据库内容时，会根据字段的类型返回值，需要检查是否影响业务代码
    + 当字段为数字类型时会返回整形
        + 检查此字段是否直接传给模型层 mapping 的 in 过滤规则
          ```php
          // 以下写法会报错
            $id = D('User')->getField('id');
            if(empty($id)){
                $map['_string'] = '1=0';
            }else{
                $map['model_id'] = ['IN', $id];
            }
          
          // 需要将 id 变为字符串或者数组
            $id = D('User')->getField('id');
            if(empty($id)){
                $map['_string'] = '1=0';
            }else{
                $map['model_id'] = ['IN', (string)$id];
            }
          ```


**移除函数其它解决方案：已理解原函数的输入输出值后创建一个同名全局函数。**

#### 需要注意使用php8.2不兼容的变更

+ 检查继承了父类/实现了接口类对应方法的返回类型，改为一致

+ 将phpstorm的php版本设置成8.2, 开启View--Tool Windows--Problems--Inspect Code 分析业务代码是否存在潜在语法错误

#### 检查是否使用了 jquery 新版 3.7 已删除方法（可参考jquery网址：https://api.jquery.com/category/removed）
+ 升级到13，升级jQuery需要关注的操作
  + 检查是否引入了下面 《think-core的修改》中已经删除的文件
  + 查看是否使用了jquery的废弃语法（如果使用到了废弃语法，如果是自己写的js，可以根据jquery文档使用同等作用的方法进行替换。如果是别人写的插件，尝试更新版本解决，如果无法通过更新版本解决，则自己写个jquery扩展方法，引入到对应的html中）
    + .andSelf()
    + .context
    + deferred.isRejected()
    + deferred.isResolved()
    + die()
    + error()
    + jQuery.boxModel
    + jQuery.browser
    + jQuery.sub()
    + live()
    + .load()
    + .selector
    + .size()
    + .toggle()
    + .unload()
    + $.get().success()、$.get().error()、$.get().complete()、$.post().success()、$.post().error()、$.post().complete()已经在3.0中被 移除，可以使用done()、fail()、always()代替

    （以上方法的详细信息可参考：https://api.jquery.com/category/removed  https://jquery.cuishifeng.cn/，以便做出更准确的判断 ）
  + 修改app/Admin/View/default/common/head.html中，将代码<a href="#" class="dropdown-toggle" data-toggle="dropdown"> 中的符号 "#" 删除
  + 在app/Admin/View/default/common/dashboard_layout.html文件的<script src="__PUBLIC__/libs/jquery/jquery.js"></script>代码下面加上代码：<script src="__PUBLIC__/libs/jquery-extend/jquery.extend.js"></script>
  + 在app/Admin/View/default/common/layout.html文件的<script src="__PUBLIC__/libs/jquery/jquery.js"></script>代码下面加上代码：<script src="__PUBLIC__/libs/jquery-extend/jquery.extend.js"></script>
  + 在 app/Home/View/default/Index/test.html文件下面删除对asset/libs/qsuploader.boundle.js的引用，因为asset/libs/qsuploader.boundle.js已经被删除
  + 在 app/Admin/View/default/common/dashboard_layout.html文件下面删除对asset/libs/messenger的引用，因为asset/libs/messenger已经被删除
  + 删除www/public/addons/Qiniu
  + 下面的文件需要查看是否被使用到，如果没有则可以直接删除，用到了就修改内容（可以全局搜索 modules/）
    + 利用assets/libs/jquery/jquery.js里面的内容替代www/Public/static/jquery-3.1.0.min.js里面的内容，同时将其名字改成www/Public/static/jquery-3.7.1.min.js，同时看看哪里引用到了就进行文件名的替换
    + 升级www/Public/modules/jquery/dist/jquery.js 为jquery3.7.1版本，文件名字保持不变
    + 将www/Public/modules/js下面的jquery-3.0.0文件夹名字改成jquery-3.7.1同时将其内部的jq.js的内容升级为jquery3.7.1。将其内部的jquery-3.0.0.min.js文件重命名为jquery-3.7.1.min.js，内容也替换成jquery3.7.1版本的内容。
    + 将www/Public/modules/js/app.js中的require('modules/js/jquery-3.0.0/jquery-3.0.0.min')替换成require('modules/js/jquery-3.7.1/jquery-3.7.1.min')
    + 将www/Public/modules/js/utils.js中的require('modules/js/jquery-3.0.0/jquery-3.0.0.min')替换成require('modules/js/jquery-3.7.1/jquery-3.7.1.min')
    + 将www/Public/views/Home/pc/js/index.js中newsFocus_hd.size()改成newsFocus_hd.length 
    + 将www/Public/views/Home/pc/modules/ignore/jquery-1.8.3/jquery.js 里面的内容升级成 jquery3.7.1，修改文件夹jquery-1.8.3名字为jquery-3.7.1
    + 将www/Public/views/Home/pc/modules/ignore/js/jquery-3.0.0/jquery-3.0.0.min.js 里面的内容升级成 jquery3.7.1，修改jquery-3.0.0/jquery-3.0.0.min.js名字为jquery-3.7.1/jquery-3.7.1.min.js
    + 修改www/Public/views/Home/pc/modules/ignore/js/fileupload/jquery.fileupload.js 里面的代码：var jQuery = require('modules/js/jquery-3.0.0/jquery-3.0.0.min'); 为 var jQuery = require('modules/js/jquery-3.7.1/jquery-3.7.1.min');
    + 修改www\Public\views\Home\pc\modules\ignore\js\fileupload\jquery.iframe-transport.js 里面的代码：var jQuery = require('modules/js/jquery-3.0.0/jquery-3.0.0.min'); 为 var jQuery = require('modules/js/jquery-3.7.1/jquery-3.7.1.min');
    + 修改 www\Public\views\Home\pc\modules\ignore\js\fileupload\jquery.ui.widget.js  里面的代码：var jQuery = require('modules/js/jquery-3.0.0/jquery-3.0.0.min'); 为 var jQuery = require('modules/js/jquery-3.7.1/jquery-3.7.1.min');
    + 将www\Public\views\Home\pc\modules\ignore\js\jquery-3.0.0下面的jquery-3.0.0文件夹名字改成jquery-3.7.1同时将其内部的jq.js的内容升级为jquery3.7.1。将其内部的jquery-3.0.0.min.js文件重命名为jquery-3.7.1.min.js，内容也替换成jquery3.7.1版本的内容。
    + 将www\Public\views\Home\pc\modules\ignore\js\app.js下面的代码：var $ = require('modules/js/jquery-3.0.0/jquery-3.0.0.min');替换成var $ = require('modules/js/jquery-3.7.1/jquery-3.7.1.min');
    + 将www\Public\views\Home\pc\modules\ignore\js\utils.js下面的代码：var $ = require('modules/js/jquery-3.0.0/jquery-3.0.0.min'); 替换成var $ = require('modules/js/jquery-3.7.1/jquery-3.7.1.min');
    + 在文件：www\Public\views\Home\pc\modules\main.js中在代码：define('modules/main', function(require, exports, module) {的下面加上代码：$.fn.size = function() {
      return this.length;
    }
    + 文件：www\Public\views\Home\pc\static\app.js，将里面的内容：define('modules/ignore/jquery-1.8.3/jquery', 替换成：define('modules/ignore/jquery-3.7.1/jquery',

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
