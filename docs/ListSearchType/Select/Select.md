## Select
```text
qscmf 列表搜索控件--select

下拉框搜索控件
```

#### 添加下拉框搜索控件

+ 简单用法
    ```php
    $data = D('User')->getField("id,name",true)
  
    (new \Qscmf\Builder\ListBuilder())
    ->addSearchItem('user_id', 'select', '用户', $data)
    ->display();
  
    ```

+ 自定义宽度，默认宽度为130px
  
  [SelectBuilder使用说明](https://github.com/quansitech/qs_cmf/tree/master/docs/ListSearchType/Select/SelectBuilder.md)

    ```php
    $data = D('User')->getField("id,name",true)
  
    // 该值为SelectBuilder对象
    $select_obj = new SelectBuilder($data);
    $select_obj->setWidth("250px");
  
    (new \Qscmf\Builder\ListBuilder())
    ->addSearchItem('user_id', 'select', '用户', $select_obj)
    ->display();
  
    ```