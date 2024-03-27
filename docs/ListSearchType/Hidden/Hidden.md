## Hidden 
```text
qscmf 列表搜索控件--hidden

隐藏搜索控件
```

### 使用方法

#### 添加控件

```php
$builder = new \Qscmf\Builder\ListBuilder();
$builder->addSearchItem('status', 'hidden');
$builder->display();
```

#### 解析请求参数

```php
//$key 搜索栏name
//$map_key 数据库字段值
//$get_data $_GET数组
//返回值 [$map_key => '参数']
$map = array_merge($map,Hidden::parse('data_key', 'map_key', $get_data);
```