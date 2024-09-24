## SelectText
```text
qscmf 列表搜索控件--select_text

字段选择搜索控件
```

### 使用方法

#### 添加控件

```php
$builder = new \Qscmf\Builder\ListBuilder();
$builder->addSearchItem('key', 'select_text', '', ['search_school_name' => '学校简称/全称','school_tag_title'=>'学校标签']));
$builder->display();
```
#### 解析请求参数

```php
use Qscmf\Builder\ListSearchType\SelectText\SelectText;

$get_data = I('get.');

  //$keys_rule 结构
  // [
  //    $key => [
  //       'map_key' => $map_key,
  //       'rule' => 'fuzzy' 模糊查找 | 'exact' 精准查找 || function(){}
  //    ]   
  // ]
  //$get_data $_GET数组
  // 返回值
  // fuzzy类型 [$map_key => ['like', "%$get_data[$key]%""]]
  // exact类型 [$map_key => $get_data[$key]]
  // 回调函数 由回调函数决定
$map = array_merge($map, SelectText::parse([
      'employee_name' => [
              'map_key' => 'employee_id',
              'rule' => function($map_key, $word){
                  $employee_sql = D('Employee')->where(['name' => ['like', '%'.$word.'%']])->field('id')->buildSql();
                  return [$map_key => ['exp', 'in '.$employee_sql]];
              }
          ],
      'reason' => [
              'map_key' => 'reason',
              'rule' => 'fuzzy'
          ]
      ], $get_data));
  ```
