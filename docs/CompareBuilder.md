## CompareBuilder

实现数据对比简化

#### 代码示例
```php
    $builder = new CompareBuilder();
        $old=[
            'title'=>'123',
            'no_change'=>'aaa',
            'html'=>'<h1>456</h1><p>123</p>'
        ];
        $new=[
            'title'=>'456',
            'no_change'=>'aaa',
            'html'=>'<h1>123</h1><p>456</p>'
        ];
        $builder->setData($old,$new)
            ->addCompareItem('title',CompareBuilder::ITEM_TYPE_TEXT,'标题')
            ->addCompareItem('no_change',CompareBuilder::ITEM_TYPE_TEXT,'没有变化')
            ->addCompareItem('html',CompareBuilder::ITEM_TYPE_HTMLDIFF,'html对比')
            ->display();
```
#### 截图
![image](https://user-images.githubusercontent.com/13673962/65034234-34b41c80-d979-11e9-8be6-6a50c546a9c2.png)
