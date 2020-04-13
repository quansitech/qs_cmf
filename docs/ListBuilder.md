## ListBuilder

addRightButton使用技巧
1. 使用占位符动态替换数据
```php
//按钮点击跳转链接，链接需要带该记录的name，只需用__name__作为占位符，生成list后会自动替换成该记录的真实name值
//如变量存在下划线，project_id，那么占位符就是 __project_id__，以此类推
->addRightButton('self', array('title' => '跳转', 'class' => 'label label-primary', 'href' => U('index', ['name' => '__name__'])));
```

### setPageTemplate
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
$title  标题
$type 表格类型（目前支持类型：status、icon、date、time、picture、type、fun、a、self）  
$value 表格value
$editable 表格是否可编辑，默认为false  
$tip 表格数据提示文字  
$th_extra_attr 表格表头额外属性  
$td_extra_attr 表格列额外属性

```
