## v13升级步骤

+ 修改composer.json文件
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
  
+ CompareBuilder FormBuilder ListBuilder 废弃display方法，使用build方法替换

+ 修改 *env* 加载方式
  ```php
  // 查找项目中 env 的创建代码
  \Dotenv\Dotenv::create
  
  // 替换成，默认为不可以改变env的值
  \Dotenv\Dotenv::createImmutable
  
  // 如修改 app/tp.php 文件中
  $dotenv = \Dotenv\Dotenv::create(__DIR__ );
  
  // 改为
  $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ );
  ```
  
+ 若实现了 showFileTitle 公共函数，需删除
 
+ 检查是否使用了 jquery 新版 3.7 已删除方法
 
+ 其他说明
  + 移除了 *Common\Util\Mail* 发送邮箱的工具
  + 移除了 *Common\Util\PDF* 
  + 移除了 *Gy_Library\DES*

+ 复制v13迁移文件 2023_11_21_015722_add_hashid_to_qs_file_pic.php 到 项目的迁移目录，执行迁移

+ 重构了脚手架的api/upload接口，接口的请求参数格式都会有变化，新增了hash_id的去重功能，同时v13 think-core 的 file picture组件也会根据新接口做重
构。复制v13的qscmf/app/Api/Controller/UploadController.php 覆盖本项目的对应文件,检查项目有无进行上传接口的客制化改造，如果有则需要对新的上传代码做同样的客制化处理。


#### 使用php8.2的修改
+ 将 DateTime::ISO8601 替换成 DateTimeInterface::ATOM

**移除函数其它解决方案：已理解原函数的输入输出值后创建一个同名全局函数。**

#### 需要注意使用php8.2不兼容的变更

+ 检查继承了父类/实现了接口类对应方法的返回类型，改为一致

+ 将phpstorm的php版本设置成8.2, 开启View--Tool Windows--Problems--Inspect Code 分析业务代码是否存在潜在语法错误


#### 扩展包修改
+ 若符合以下修改要求，需要同步修改 composer.json ，限制 think-core 为 v13 的最新版本

+ **检查实现了 EditableInterface 的 ColumnType，将 builder 传给 getSaveTargetForm 方法。**

+ **检查继承了 ButtonType 的 TopButton，将 build 改成与父类一致。**  

  