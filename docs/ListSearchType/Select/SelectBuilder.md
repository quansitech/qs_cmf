## SelectBuilder
```text
设置select的属性
```

#### 实例化类
```php
// 参数说明
// array $data 下拉框选项
$data = ['1' => '数据1', '3' => '数据3'];
new SelectBuilder($data);
```

#### setData
```text
设置下拉框选项
```
```php
// 参数说明
// array $data 下拉框选项
$data = ['1' => '数据1', '3' => '数据3'];
->setData($data)
```

#### setPlaceholder
```text
设置下拉框提示
```
```php
// 参数说明
// string $placeholder 下拉框提示
$placeholder = "请选择";
->setPlaceholder($placeholder)
```

#### getPlaceholder
```text
获取下拉框提示
```
```php
->getPlaceholder()
```

#### setWidth
```text
设置下拉框宽度，默认为130px
```
```php
// 参数说明
// string $width 下拉框宽度
$width = '100px';
->setWidth($width)
```