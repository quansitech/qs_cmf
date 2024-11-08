## Model

### auto

增加传递新增记录给function或者callback的方法

```php
protected $_auto = array(
    //第六个参数设置为true，可以接收到新增的记录
    //此时如果不需要通过第五个参数额外传递数据，可设置为null
    ['sample_filed', 'sampleCallback', parent::MODEL_INSERT, 'callback', null, true]
);
```

### 封印字段

可设置不可修改和新增的字段

场景：有些字段是由触发器维护的，应用层不应该修改这些字段。但随着系统开发的深入，维护时间的增加。经常会忘记了这个原则，导致了不经意间修改了该字段，产生了难以调试的bug。

用法：

```php
//在model设置 触发器维护字段order_no
protected $_seal_fields = [
    'order_no'
];
```

### 清除数据库缓存

clearCache 方法

参数options 通过该参数可以设置Modal对象的options属性

## 删除验证

删除数据前，验证是否满足删除条件，如果不满足则禁止删除，并返回不能删除的原因

1. 存在数据，则禁止删除

   检查指定的表和关联字段，如果存在数据，则禁止删除

   ```php
   protected $_delete_validate = array(
       array('Access', 'role_id', parent::EXIST_VALIDATE , '请先清空用户组权限'),
       array('RoleUser', 'role_id', parent::EXIST_VALIDATE , '请先清空用户组下面的用户数据'),
   );
   ```

   设定回调函数，回调函数返回更复杂的信息验证，返回数据结果集，根据验证策略决定是否可删除

   ```php
   protected $_delete_validate = array(
       array(function($ids){
           return D("Access")->where(['role_id', ['in', $ids]])->select();
       }, '', parent::EXIST_VALIDATE, '请先清空用户组权限')  
   );
   ```

2. 不存在数据，则禁止删除

   用法同1，第三个参数改成 parent::NOT_EXIST_VALIDATE

   ```php
   protected $_delete_validate = array(
       array('Access', 'role_id', parent::NOT_EXIST_VALIDATE, '缺少数据，不能删除')
   );
   ```

3. 指定字段，当该字段值为设定范围时，禁止删除

   ```php
   protected $_delete_validate = array(
       array(array('2'), 'group', parent::NOT_ALLOW_VALUE_VALIDATE, '不能删除系统组的配置项'),
   );
   ```

4. 指定字段，当该字段值为设定范围时，才允许删除

   ```php
   protected $_delete_validate = array(
       array(array('2'), 'group', parent::ALLOW_VALUE_VALIDATE, '不能删除系统组的配置项'),
   );
   ```



## 联动删除

在进行一些表删除操作时，很可能要删除另外几张表的特定数据。联动删除功能只需在Model里定义好联动删除规则，在删除数据时即可自动完成另外多张表的删除操作，可大大简化开发的复杂度。

使用样例

```php
//假设有一张文章表Post, 评论表 Message, 点赞表 Like。 
//点赞表有字段type, type_id, 当type=4时，type_id指向文章表的主ID
//评论表有字段post_id，post_id与文章表的主id关联
//这时我们可以在Model里定义 _initialize方法，在该方法内定义 _delete_auto 规则
protected function _initialize() {
    $this->_delete_auto = [
         //实现Message表的联动删除
        ['delete', 'Message', ['id' => 'post_id']],
        //实现点赞表的联动删除，$ent参数就是存放Post表的记录的实体变量
        ['delete', function($ent){
            D('Like')->where(['type' => 4, 'type_id' => $ent['id']])->delete();
        }],
    ];
}

//如需指定删除联动数据失败时的操作，默认为继续删除，请指定第四参数，如:
$this->_delete_auto = [
    ['delete','Message',['id'=>'post_id'],[
        'error_operate'=>self::DELETE_CONTINUE //继续删除
    ]],
    //或
    ['delete','Message',['id'=>'post_id'],[
        'error_operate'=>self::DELETE_BREAK //删除停止并返回错误信息
    ]],
];
```

目前联动删除的定义规则暂时只有两种，第二种规则比第一种规则更灵活，可应用于更多复杂的场景。第一种规则仅能应用在两个表能通过一个外键表达关联的场景。第一种规则在性能上比第二种更优。

## TP数据库

+ 原生sql写法 Db::Raw

  > tp会对字符串的sql进行分析，对关键字自动加上``，但做的并不好，会将table方法和field方法的sql解析出语法问题
  >
  > 因此加入了 Db::Raw的方法来阻止tp解析
  >
  > ```php
  > D('Test')
  > ->table(Db::Raw(( 'SELECT @box_id:=null, @rank:=0 ) a'))
  > ->field(Db::Raw('tmp.*, if(@box_id=tmp.box_id, @rank:=@rank+1, @rank:=1) AS rk, @box_id:=tmp.box_id'))
  > ```

+ 新增DB_STRICT模式, 在app/Common/Conf/config.php 设置true开启

  ```php
  'DB_STRICT'             =>  env('DB_STRICT', true)
  ```

strict模式默认启动一下设置

'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'

而非strict模式是

'NO_ENGINE_SUBSTITUTION'

## 写数据回调函数添加钩子
```text
新增、更新、删除数据后对应回调添加钩子，方便扩展包自定义处理
```
+ _after_insert 监听钩子：after_insert，参数$params见说明
+ _after_update 监听钩子：after_update，参数$params见说明
+ _after_delete 监听钩子：after_delete，参数$params见说明

```php
// $params说明
// 该变量为数组，元素键说明

// model_obj model对象 
// data 数据信息
// options 查询表达式参数
```

## 数据库帮助函数

generator

> 低内存消耗迭代函数
>
> 参数
>
> 1. $map 查询参数 默认为空数组
> 2. $count 一次查询的数据量，越大占用的内存会大，但运行效率会更高，根据情况灵活调整 默认为 1
>
> 举例
>
> ```php
>   foreach(D("Content")->generator([], 200) as $ent){
>      var_dump($ent);
>   }
> ```

getFieldForN1

> 将循环N次select转成1次select
>
> 参数
>
> 1. $map 查询参数 默认为空数组
> 2. $field 查询的字段列表
> 3. $primary_key 查询的数据主键
> 4. $show_field 需要返回的字段内容
>
> 举例
>
> ```php
>   $orders = D("Order")->select();
>   $person_ids = collect($orders)->pluck('person_id')->toArray();
>   foreach($orders as $ent){
>      $ent['person_name'] = D("Person")->getFieldForN1(['id' => ['in', $person_ids]], 'id,name', $ent['person_id'], 'name');
>   }
> ```