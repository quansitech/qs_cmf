# Self
```text
qscmf 列表搜索控件--self

自定义搜索控件
```

## 字段说明
- content
```text
当需求比较简单，可以将html/css/js写入此字段。
```
- templateFile
```text
当需求比较复杂，或者需要复用其它html模板，可以将文件路径写入此字段。
```
content优先于templateFile，当content非空时，templateFile不会生效。


## 用法示例
示例1：
使用content
```php
$builder = new \Qscmf\Builder\ListBuilder();
$builder->addSearchItem('field_name','self','字段标题',['content'=>'<script>alert("自定义搜索组件示例");</script>']);

```
示例2：
使用templateFile
```php
$builder = new \Qscmf\Builder\ListBuilder();
$builder->addSearchItem('field_name','self','字段标题',['templateFile'=>'Self/templateA']);

//'Self/templateA'的路径是app/Admin/View/default/Self/templateA.html

//如需传入模板变量,可在数组中传入，如下面变量var1，在模板中通过{$item['options']['var1']}
$builder->addSearchItem('field_name','self','字段标题',['templateFile'=>'Self/templateA','var1'=>'var1_value']);

```

