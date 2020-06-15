## SubBuilder

#### 用法

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