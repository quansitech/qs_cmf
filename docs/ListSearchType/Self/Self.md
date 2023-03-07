# Self
```text
qscmf 列表搜索控件--self

自定义搜索控件
```

## 参数说明
- content
```text
当需求比较简单，可以将html/css/js写入此参数。
```
- templateFile
```text
当需求比较复杂，或者需要复用其它html模板，可以将文件路径写入此参数。
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

```

