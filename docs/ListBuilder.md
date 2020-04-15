## ListBuilder

#### addRightButton
```blade
加入一个数据列表右侧按钮

在使用预置的几种按钮时，比如我想改变编辑按钮的名称
那么只需要$builder->addRightButton('edit', array('title' => '换个马甲'))
如果想改变地址甚至新增一个属性用上面类似的定义方法
因为添加右侧按钮的时候你并没有办法知道数据ID，于是我们采用__data_id__作为约定的标记
__data_id__会在display方法里自动替换成数据的真实ID

参数
$type 按钮类型，取值参考registerBaseRightButtonType
$attribute 按钮属性，一个定义标题/链接/CSS类名等的属性描述数组
$tips 按钮提示
$auth_node 按钮权限点
$options 按钮options

若auth_node存在多个值，支持配置不同逻辑（logic值为and或者or）判断是否显示该按钮，默认为and：
and：用户拥有全部权限则显示该按钮，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'and']
or：用户一个权限都没有则隐藏该按钮，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'or']
```

```blade
使用技巧
1. 使用占位符动态替换数据
```

```php
//按钮点击跳转链接，链接需要带该记录的name，只需用__name__作为占位符，生成list后会自动替换成该记录的真实name值
//如变量存在下划线，project_id，那么占位符就是 __project_id__，以此类推
->addRightButton('self', array('title' => '跳转', 'class' => 'label label-primary', 'href' => U('index', ['name' => '__name__'])));
```

#### setPageTemplate
```blade
该方法用于设置页码模板

参数
$page_template 页码模板自定义html代码
```

#### addTableColumn
```blade
该方法用于加一个表格列标题字段

参数
$name 列名 
$title 列标题
$type 列类型，默认为null（目前支持类型：status、icon、date、time、picture、type、fun、a、self）  
$value 列属性，默认为''，一个定义标题/链接/CSS类名等的属性描述数组
$editable 列是否可编辑，默认为false  
$tip 列标题提示文字，默认为''  
$th_extra_attr 列标题额外属性，默认为''  
$td_extra_attr 列值额外属性，默认为''
$auth_node 列权限点，需要先添加该节点，若该用户无此权限则unset该列；格式为：模块.控制器.方法名，如：['admin.Box.allColumns']

若auth_node存在多个值，支持配置不同逻辑（logic值为and或者or）判断是否显示该列，默认为and：
and：用户拥有全部权限则显示该列，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'and']
or：用户一个权限都没有则隐藏该列，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'or']
```

#### addTopButton
```blade
加入一个列表顶部工具栏按钮

在使用预置的几种按钮时，比如我想改变新增按钮的名称
那么只需要$builder->addTopButton('addnew', array('title' => '换个马甲'))
如果想改变地址甚至新增一个属性用上面类似的定义方法

参数
$type 按钮类型，取值参考registerBaseTopButtonType
$attribute 按钮属性，一个定义标题/链接/CSS类名等的属性描述数组
$tips 按钮提示
$auth_node 按钮权限点
$options 按钮options

若auth_node存在多个值，支持配置不同逻辑（logic值为and或者or）判断是否显示该按钮，默认为and：
and：用户拥有全部权限则显示该按钮，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'and']
or：用户一个权限都没有则隐藏该按钮，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'or']
```