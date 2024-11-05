# v14升级步骤

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
www/Public/backend/.gitignore
```

* 在app/Admin/View/default/common/dashboard_layout.html前面增加下列代码：

```html
<taglib name="Qscmf\Lib\Inertia\TagLib\Inertia" />
```

* 安装依赖

```shell
composer require quansitech/qscmf-buttontype-modal
composer require quansitech/antd-admin
```

* 若要开启antd-admin，请检查 `quansitech/qscmf-formitem-ueditor` 版本为 `^2.0` 或以上

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
app/Admin/Controller/DashboardController.class.php
app/Admin/Controller/RoleController.class.php
app/Admin/Controller/UserController.class.php
```