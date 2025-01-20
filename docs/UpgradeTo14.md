# v14升级步骤

* 修改composer.json文件

```text
修改 require 以下扩展版本
"tiderjian/think-core":"^14.0.0"
如果用了 overtrue/wechat 需要升级到 w7corp/easywechat ^6.7

修改 require-dev 以下扩展版本
"phpunit/phpunit": "^10.0"
"laravel/dusk": "^v8.2.5"
```


* 复制14版本的下列文件到你的项目对应目录下：

```text
app/Admin/View/default/common/inertia_layout.html
app/Admin/View/default/common/inertia_blank_layout.html
app/Home/View/app.html
package.json
resources/js
tsconfig.json
vite.backend.config.js
vite.config.js
```

* 在app/Admin/View/default/common/dashboard_layout.html前面增加下列代码：

```html
<taglib name="Qscmf\Lib\Inertia\TagLib\Inertia" />
```

* 安装依赖

```shell
composer require quansitech/qscmf-buttontype-modal ^4.0
composer require quansitech/antd-admin
```

* 若要开启antd-admin，请检查 `quansitech/qscmf-formitem-ueditor` 版本为 `^3.0` 或以上
* 如果使用了 qscmf-formitem-object-storage 也需要升级到 ^3.0

* 增加下列配置到app/Common/Conf/config.php配置文件里：

```php
    'INERTIA' => [
        'ssr_url' => env('INERTIA_SSR_URL'),
    ]
```

* 增加head块到 app/Admin/View/default/common/dashboard_layout.html 中：

```html
...
<head>
    ...
    <!-- 增加代码 -->
    <block name="head"></block>
    <!-- 增加代码结束 -->
</head>
...
```

* 将下列文件代码合并到项目中，自行检查是否有客制化代码：

```
app/Admin/Conf/config.php
app/Admin/Controller/DashboardController.class.php
app/Admin/Controller/RoleController.class.php
app/Admin/Controller/UserController.class.php
```

* 部分客制化较强的页面，需要用到原页面渲染，则在方法中临时修改配置即可

```php
C('ANTD_ADMIN_BUILDER_ENABLE', false);
C('ANTD_ADMIN_NEW_LAYOUT', false);
```

* composer包替换，可能需要安装npm包，请查看具体文档安装

1. [tiderjian/qs-grid-import](https://github.com/quansitech/qs-grid-import) -> [quansitech/antd-admin-grid-import](https://github.com/quansitech/antd-admin-grid-import)
2. [quansitech/qscmf-topbutton-export](https://github.com/quansitech/qscmf-topbutton-export) -> [quansitech/antd-admin-table-action-export](https://github.com/quansitech/antd-admin-table-action-export)
