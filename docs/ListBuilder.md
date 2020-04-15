## ListBuilder

#### addRightButton
```blade
加入一个数据列表右侧按钮

在使用预置的几种按钮时，比如我想改变编辑按钮的名称
那么只需要$builder->addRightButton('edit', array('title' => '换个马甲'))
如果想改变地址甚至新增一个属性用上面类似的定义方法
因为添加右侧按钮的时候你并没有办法知道数据ID，于是我们采用__data_id__作为约定的标记
__data_id__会在display方法里自动替换成数据的真实ID

$type 按钮类型，edit/forbid/recycle/restore/delete/self六种取值
$attribute 按钮属性，一个定了标题/链接/CSS类名等的属性描述数组
$tips 按钮提示
$auth_node 字段权限点
$options 字段options

若auth_node存在多个值，支持配置不同逻辑（logic值为and或者or）判断是否显示该表单，默认为and：
and：用户拥有全部权限则显示该表单，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'and']
or：用户一个权限都没有则隐藏该表单，格式为：
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
该方法用于加一个表格标题字段

参数
$name 表格名 
$title 表格标题
$type 表格类型，默认为null（目前支持类型：status、icon、date、time、picture、type、fun、a、self）  
$value 表格value，默认为''，当type为使用fun/a/self时有效，value为其属性值
$editable 表格是否可编辑，默认为false  
$tip 表格数据提示文字，默认为''  
$th_extra_attr 表格表头额外属性，默认为''  
$td_extra_attr 表格列额外属性，默认为''
$auth_node 字段权限点，需要先添加该节点，若该用户无此权限则unset该表单；格式为：模块.控制器.方法名，如：['admin.Box.allColumns']

若auth_node存在多个值，支持配置不同逻辑（logic值为and或者or）判断是否显示该表单，默认为and：
and：用户拥有全部权限则显示该表单，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'and']
or：用户一个权限都没有则隐藏该表单，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'or']
```
