## Text 
```text
qscmf 列表搜索控件--text

文本搜索控件
```

### 使用方法

#### 添加控件

```php
$builder = new \Qscmf\Builder\ListBuilder();
$builder->addSearchItem('title', 'text','标题');
$builder->display();
```

#### 解析请求参数

```php
  //$key 搜索栏name
  //$map_key 数据库字段值
  //$get_data $_GET数组
  //$rule 'fuzzy' 模糊查找 | 'exact' 精准查找
  // 返回值
  // fuzzy类型 [$map_key => ['like', "%$get_data[$key]%""]]
  // exact类型 [$map_key => $get_data[$key]]
  $map = array_merge($map, Text::parse('text', 'text', $get_data, 'exact'));
```