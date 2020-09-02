## SubBuilder

#### 用法

#### 只读模式
实例SubTableBuilder对象时可设置为只读模式，默认是关闭
```php
$sub_builder = new \Qscmf\Builder\SubTableBuilder(true);
```

#### addTableHeader
```blade
该方法用于加入一个子表单项的标题

参数
$name 名称
$width 该项占用整行宽度的比例
```
```php
$sub_builder = new \Qscmf\Builder\SubTableBuilder();
$sub_builder = $sub_builder
        ->addTableHeader('标题', '30%')
        ->addTableHeader('摘要', '30%');
```

#### addFormItem
```blade
该方法用于加入一个子表单项的内容

参数
$name item名
$type item类型（查看支持类型）
$options item options
$readonly 是否开启只读模式，默认关闭，false
$extra_class item项额外样式
$extra_attr item项额外属性
```
```php
$sub_builder = new \Qscmf\Builder\SubTableBuilder();
$sub_builder = $sub_builder
        ->addTableHeader('标题', '30%')
        ->addTableHeader('摘要', '30%')
        ->addFormItem('id', 'hidden')
        ->addFormItem('title', 'text')
        ->addFormItem('summary', 'textarea');
```

#### setData
```blade
设置子表单项的数据
```
```php
$data = [
    ['name' => 'id', 'type' => 'hidden', 'value' => 1],
    ['name' => 'title', 'type' => 'text', 'value' => 'title'],
    ['name' => 'summary', 'type' => 'textarea', 'value' => 'summary']
];

$sub_builder = new \Qscmf\Builder\SubTableBuilder();
$sub_builder = $sub_builder
        ->addTableHeader('标题', '30%')
        ->addTableHeader('摘要', '30%')
        ->addFormItem('id', 'hidden')
        ->addFormItem('title', 'text')
        ->addFormItem('summary', 'textarea')
        ->setData($data);
```

#### makeHtml
```blade
返回所有表单项的html，可以根据需要嵌入FormBuilder
```

#### 类型说明

+ 支持类型
> 1. checkbox
>> + 多选框，目前支持单个
> 2. text
>> + 文本类型
> 3. hidden
>> + 隐藏
> 4. select
>> + 下拉选择
> 5. select2
>> + 下拉选择，支持模糊搜索
> 6. textarea
>> + 多行文本

+ select2新增了自定义标签功能
```php
$subBuilder = new \Qscmf\Builder\SubTableBuilder();
$subBuilder = $subBuilder -> addTableHeader('关键词', '30%')
                -> addFormItem('keywords_id', 'select2', [
                    'tags' => true,
                    'options' => [ 1 => '测试', 2 => '测试2']
                    ]);

$builder = new FormBuilder();
$builder->addFormItem('keywords_id', 'self', '关键词','',$subBuilder->makeHtml());
```