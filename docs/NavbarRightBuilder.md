## NavbarRightBuilder
生成header右侧导航项

#### 用法
+ 生成一个右上角带有数字，点击可跳转的导航项
  ```php
  (new \Qscmf\Builder\NavbarRightType\NavbarRightBuilder())
        ->setType('num')
        ->setAttribute(['title' => '待审核数', 'href' => U('UserTest/index')])
        ->setOptions(100)
  ```

+ 生成一个自定义html的导航项
  ```php
  $url = U('admin/UserTest/index');
  $self = <<<self
  <a title="待审核数" href="{$url}">待审核数<span class="number">100</span></a>
  self;
  
  (new \Qscmf\Builder\NavbarRightType\NavbarRightBuilder())
        ->setType('self')
        ->setOptions($self)
        ->setLiAttribute(['class'=>"dropdown user user-menu"])
  ```


#### setType
```text
设置导航项类型

参数说明
$type 类型，取值参考方法(new \Qscmf\Builder\HeaderBuilder())->registerNavbarRightLiType()
```

#### setAttribute
```text
设置导航项属性

参数说明
$attribute 属性，一个定义标题/链接/CSS类名等的属性描述数组
```

```php
->setAttribute(['title' => '待审核数', 'href' => U('UserTest/index')])
```

#### setAuthNode
```text
设置导航项权限点规则，符合权限要求才展示

参数说明
$auth_node 权限点规则

若auth_node存在多个值，支持配置不同逻辑（logic值为and或者or）判断是否显示该按钮，默认为and：
and：用户拥有全部权限则显示该按钮，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'and']
or：用户一个权限都没有则隐藏该按钮，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'or']
```

```php
->setAuthNode('admin.user.index')

->setAuthNode(['node' => ['admin.user.index','admin.user.add'], 'logic' => 'and'])
```

#### setOptions
```text
设置导航项options，根据type定义

参数说明
$options 设置导航项options
```

#### setLiAttribute
```text
设置导航项父级Li属性

参数说明
$li_attribute 定义CSS类名等的属性描述数组
```

```php
->setLiAttribute(['class'=>"dropdown user user-menu"])
```
