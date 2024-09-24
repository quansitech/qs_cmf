## SelectCity
```text
qscmf 列表搜索控件--select_city

城市搜索控件
```

### 使用方法

#### 添加控件

```php
$builder = new \Qscmf\Builder\ListBuilder();
$builder->addSearchItem('city_id', 'select_city', '所在地');
$builder->display();
```

#### 配置选择器级别

接受一个 `level` 选项，用于指定选择器中城市的级别。可选的级别是 1 到 4，分别代表不同级别的行政区划（省份、城市、区县、街道）。默认级别为 3。
```php
$builder->addSearchItem('city_id', 'select_city', '所在地', ['level' => 2]);
```
#### 解析请求参数


```php
//$key 搜索栏name
//$map_key 数据库字段值
//$get_data $_GET数组
//返回值 [$map_key => '参数']
$map = array_merge($map,SelectCity::parse('data_key', 'map_key', $get_data);
```