# Self
```text
qscmf 列表搜索控件--self

自定义搜索控件
```

## 字段说明
- value
```text
可以将html/css/js写入此字段。
```


## 用法示例
```php
$builder = new \Qscmf\Builder\ListBuilder();
$builder->addSearchItem('field_name','self','字段标题',['value'=>'<script>alert("自定义搜索组件示例");</script>']);

```


