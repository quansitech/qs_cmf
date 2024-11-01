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

* 增加下列配置到app/Common/Conf/config.php配置文件里：

```php
    'INERTIA' => [
        'ssr_url' => env('INERTIA_SSR_URL'),
    ]
```

## 开启 antd-admin

* 配置 `app/Admin/Conf/config.php`

```php
return [
    //...省略
    
    'ANTD_ADMIN_BUILDER_ENABLE' => true, // 是否开启Antd Admin Builder
    'ANTD_ADMIN_NEW_LAYOUT' => true, // 是否开启Antd Admin 新布局
];
```