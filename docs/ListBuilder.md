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
$type 列类型，默认为null（目前支持类型：status、icon、date、time、picture、pictures、type、fun、a、self、num、checkbox、select、select2、textarea）  
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

##### type类型使用说明
1. date
> + 通过value设置转换的日期格式，默认为'Y-m-d'
2. time
> + 通过value设置转换的日期格式，默认为'Y-m-d H:i:s'
3. pictures
> + 列表多图展示，缩略图默认使用原图，可通过value设置缩略图代理：'oss'、'imageproxy'


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

#### addSearchItem
```blade
加入一个筛选项，提交的url默认为当前页

参数
$name 筛选项字段
$type 筛选项类型（目前支持类型可查看ListSearchType）  
$title 筛选项提示标题
$options 筛选项其它配置，特殊类型有效，如select、select_text
$auth_node 筛选项权限点

若auth_node存在多个值，支持配置不同逻辑（logic值为and或者or）判断是否显示该筛选项，默认为and：
and：用户拥有全部权限则显示该按钮，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'and']
or：用户一个权限都没有则隐藏该按钮，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'or']

用例：
->addSearchItem('keyword', 'text', 'id/昵称/email/手机号');

用法：
可以通过设置search元素data-jump属性（1为跳转，0为不跳转），确定点击该元素后是否跳转。

使用技巧：
可以给search元素添加”beforeSearch“事件，处理搜索前的动作。

场景：
用户没有填写搜索关键字，点击搜索需要提醒用户搜索关键字不能为空且不跳转。
```

```javascript
$('body').on('beforeSearch', '.builder #search', function() {
    var keyword = $("input[name='keyword']").val();

    if (!keyword){
        $(this).data('jump', 0);
        alert('关键字不能为空');
    }
});
```

#### setSearchUrl
```blade
设置筛选提交的url
```

#### setLockRow
```blade
锁定行
用法
->setLockRow(1)
参数
$row 锁定行数
```

#### setLockCol
```blade
锁定列（左）
用法
->setLockCol(1)
参数
$row 锁定列数
```

#### setLockColRight
```blade
锁定列（右）
用法
->setLockCol(1)
参数
$row 锁定列数
```

#### QsPage

开启关闭下拉风格， 默认采用数字分页风格

QsPage分数字分页风格和下拉分页风格，两种风格对分页有不同的需求。 

1. 数字分页风格限制了不能访问超出最大页数的页面，否则仅返回最大页（需求场景：用户删除最大页的所有数据后，程序会刷新停留在当前页，此时用户会看到是空数据页，会产生已经没有数据的误解。）。

2. 下拉风格页没有访问超出最大页数的限制，否则下拉程序会不断加载最大页的内容，导致无限加载相同的数据。



开启下拉风格

```
QsPage::setPullStyle(true) 
```
