## DateRange
```text
qscmf 列表搜索控件--`date_range`

日期范围搜索控件
```
### 用法示例

#### 添加控件

```php
$builder = new \Qscmf\Builder\ListBuilder();
$builder
    ->addSearchItem('create_date', 'date_range', '创建时间')
    ->display();
```

#### 配置可选中时分

将 `time_picker` 设置为 `true` 即可启用时间选择器。

```php
$builder
    ->addSearchItem('create_date', 'date_range', '创建时间', ['time_picker' => true])
    ->display();
```

#### 解析请求参数

```php
//$key 搜索栏name
//$map_key 数据库字段值
//$get_data $_GET数组
//返回值 [$map_key => ['BETWEEN', [$start_time, $end_time]]]
$map = array_merge($map, DateRange::parse('data_key', 'map_key', $get_data));
```