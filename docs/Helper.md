### QsPage

开启关闭下拉风格， 默认采用下拉风格

QsPage分数字分页风格和下拉分页风格，两种风格对分页有不同的需求。

1. 数字分页风格限制了不能访问超出最大页数的页面，否则仅返回最大页（需求场景：用户删除最大页的所有数据后，程序会刷新停留在当前页，此时用户会看到是空数据页，会产生已经没有数据的误解。）。

2. 下拉风格页没有访问超出最大页数的限制，否则下拉程序会不断加载最大页的内容，导致无限加载相同的数据。


开启下拉风格

```
//设置默认风格（不显式设置得默认值）
QsPage::setDefaultPullStyle(true) 

//按需求重置风格
$page = new QsPage($total, $per_page_rows);
$page->setPullStyle(false)
```


### 全局函数

#### fork
框架如果直接用pcntl_fork创建子进程时，进行数据库操作时会出错，直接使用该函数可避免这些问题.

#### isUrl
验证是否合法的url

#### normalizeRelativePath
将相对路径转成绝对路径

#### removeFunkyWhiteSpace
清除一些奇怪无法看到的空格

#### getRelativePath
计算$b相对于$a的相对路径，如$b = /a/b/c $a = /a/d/f 那么返回 ../d/f

#### base64_url_encode
对url进行base64转码，转码前会先对+ /，进行处理

#### base64_url_decode
将base64转回url，与base64_url_encode搭配使用

#### showThumbUrl

```php
参数
$file_id 存放在qs_file_pic表的图片id
$prefix 缩略图的前缀，与裁图插件的前缀相对应
$replace_img 如获取图片失败，适应该指定的图片url代替

返回值
图片url地址

该函数一般用于获取裁剪插件所裁出来的本地缩略图。
如果$file_id对应的是用seed功能填充出来的图片，还可以依据前缀获取到所希望图片的大小，自动构造相同的大小的图片url。
使用该函数即可在不做任何代码改动的情况下完好的作用在本地图片上传和填充伪造图片的两种场景。
```

#### showFileUrl

```blade
参数
$file_id 存放在qs_file_pic表的文件id，若为url,则返回该url
$default_file 默认文件的URL地址

返回值
文件的URL地址

该函数一般用于展示数据库存储文件的URL地址。

如果上传策略的security设置成true

第三方组件包可以注册 get_auth_url事件来自定义文件的临时授权访问地址

$param = [
    'file_ent' => $file_pic_ent, //qs_file_pic 记录
    'timeout' => 60  //授权链接过期时间
];
\Think\Hook::listen('get_auth_url', $param);

返回 $params['auth_url'] 即为临时授权链接

```



#### showImg

```blade
参数
$file_id 存放在qs_file_pic表的图片id，若为url,则返回该url

返回值
图片的URL地址

该函数一般用于展示数据库存储图片的URL地址，若不存在图片则返回系统配置默认封面。
```

#### cleanRbacKey
清空INJECT_RBAC标识key的session值

#### uniquePageData
分页去重辅助函数
```php
$member_list = D('Person')->getList($map, $page, $per_page);

//第一个参数，数据缓存唯一标识
//第二个参数，数据唯一键值
//第三个参数，第几页
//第四个参数，去重数据
$member_list = uniquePageData('Person->getList', 'id', $page, $member_list);
```

#### D
```blade
实例化模型类

参数
$name 模型名称，为''则实例化Think\Model
$layer 模型层名称，为''则使用DEFAULT_M_LAYER值
$close_type 关闭类型，默认为false，可通过\Qscmf\Lib\DBCont::getCloseTypeList()查看目前支持的类型
```

```php
// 使用技巧
// 需要关闭所有的数据库连接（以下写法等效）
D('','', true);
D('','', \Qscmf\Lib\DBCont::CLOSE_TYPE_CONNECTION);

// 若修改了数据库连接的配置，要使全部模型生效，则需要同时关闭数据库连接和清除已实例化的模型
D('','', \Qscmf\Lib\DBCont::CLOSE_TYPE_ALL);
```

#### getAreaFullCname1ByID
```blade
根据地区id获取其中文全名称

参数
$id 地区id
$model 模型层名称，默认为AreaV（需要检查模型层对应的数据表是否存在）
$field 字段名称，默认为full_cname1
```

#### getAllAreaIdsWithMultiPids
```blade
根据多个地区id获取其下属的所有地区

参数
$ids 地区id
$model 模型层名称，默认为AreaV（需要检查模型层对应的数据表是否存在）
$max_level 最大级数据，默认为区级，即3（省级为1，市级为2，区级为3）
$need_exist 是否只需要模型层对应数据表存在的数据，默认为true（如id为123的城市并不存在，若该值为true，返回结果并不包括它；若该值为false，返回结果则包括）
$cache 开启数据库缓存，默认为空，即不开启（可支持传数组、字符串、数字）

cache参数说明：若需要开启缓存且缓存60秒，则传数组[true,60,'']或者字符串'60'或者数字60。
```

#### parseModelClsName
```blade
解析模型类的类名

参数
$name string 资源地址
$layer string 模型层名称，默认为C('DEFAULT_M_LAYER')
```

```php
// 实例化一个模型类ConfigModel
$ref_model_cls = parseModelClsName('Config');
$ref_model_cls = new $ref_model_cls('Config');
$ref_model_cls->getOne();
```

#### deleteEmptyDirectory
```text
递归删除目录下的空目录

参数
$directory string 目录路径
$preserve bool 是否保留本身目录，默认为false，不保留
```

#### filterItemsByAuthNode
+ 说明
  
    根据权限点过滤数组元素
    
    | 参数     | 说明                           | 类型 | 必填 | 默认值 |
    | :------- | :----------------------------- | ---- | ---- | :----- |
    | check_items | 内容 |  array | 是   |   |
    | check_items.auth_node | 权限点，格式为 模块.控制器.方法名 |  string I array | 否   |   |
    
    若auth_node存在多个值，支持配置不同逻辑（logic值为and或者or）判断是否显示，默认为and：
    
    and：用户拥有全部权限则显示，格式为：
    ['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'and']
    
    or：用户一个权限都没有则隐藏，格式为：
    ['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'or']


#### filterOneItemByAuthNode
+ 说明
  
    根据权限点过滤内容
    
    | 参数     | 说明                           | 类型 | 必填 | 默认值 |
    | :------- | :----------------------------- | ---- | ---- | :----- |
    | item | 内容 |  string | 是   |   |
    | item_auth_node | 权限点，格式为 模块.控制器.方法名 |  string I array | 否   |   |
    
    若item_auth_node存在多个值，支持配置不同逻辑（logic值为and或者or）判断是否显示，默认为and：
    
    and：用户拥有全部权限则显示，格式为：
    ['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'and']
    
    or：用户一个权限都没有则隐藏，格式为：
    ['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'or']


#### isJson
判断字符串是否为json格式



#### S

缓存操作便捷函数, 见[[数据缓存 - ThinkPHP3.2完全开发手册](http://document.thinkphp.cn/manual_3_2.html#data_cache)]

在此基础上，增加了第四个参数，是否保留缓存的过期时间。默认为false，开启的话设置true，开启后如果key有未过期，则保留当前的过期时间，不做刷新处理；否则将expire作为新的过期时间设置。

#### getNid
```blade
获取node id，用于后台高亮菜单

例如设置导入数据页面的高亮菜单

参数
$module_name string 模块名，默认为常量值MODULE_NAME，即当前模块
$controller_name string 控制器名，默认为常量值CONTROLLER_NAME，即当前控制器
$action_name string 方法名，默认为常量值ACTION_NAME，即当前方法
```

#### extractParamsByUrl
```blade
提取url参数

参数
$url string url
$filter_empty bool 是否过滤空值，true 是 false 否，默认为false
```

#### combineOssUrlImgOpt
```blade
拼接阿里云oss图片处理参数

参数
$url string url
$opt string 图片处理参数，如'x-oss-process=image/resize,w_100'或者'resize,w_100'
```
用例
```php
  $url1 = 'https://quansi-test.oss-cn-shenzhen.aliyuncs.com/Uploads/image/20220721/62d91a961c13a.heic';
  $url2 = 'https://quansi-test.oss-cn-shenzhen.aliyuncs.com/Uploads/image/20220721/62d91a961c13a.heic?a=1';
  $url3 = 'https://quansi-test.oss-cn-shenzhen.aliyuncs.com/Uploads/image/20220721/62d91a961c13a.heic?x-oss-process=image/format,jpg';
  $url4 = 'https://quansi-test.oss-cn-shenzhen.aliyuncs.com/Uploads/image/20220721/62d91a961c13a.heic?a=1&x-oss-process=image/format,jpg';
    
   $opt = 'x-oss-process=image/resize,w_100';
    
    dd(
        combineOssUrlImgOpt($url1,$opt)
        ,combineOssUrlImgOpt($url2,$opt)
        ,combineOssUrlImgOpt($url3,$opt)
        ,combineOssUrlImgOpt($url4,$opt)
    );

// 输出
// "https://quansi-test.oss-cn-shenzhen.aliyuncs.com/Uploads/image/20220721/62d91a961c13a.heic?x-oss-process=image/resize,w_100"
// "https://quansi-test.oss-cn-shenzhen.aliyuncs.com/Uploads/image/20220721/62d91a961c13a.heic?a=1&x-oss-process=image/resize,w_100"
// "https://quansi-test.oss-cn-shenzhen.aliyuncs.com/Uploads/image/20220721/62d91a961c13a.heic?x-oss-process=image/format,jpg/resize,w_100"
// "https://quansi-test.oss-cn-shenzhen.aliyuncs.com/Uploads/image/20220721/62d91a961c13a.heic?a=1&x-oss-process=image/format,jpg/resize,w_100"
```

#### reorderRowKey

```blade
重排二维数组中一维数组的键
```

参数

| 名称   | 说明      | 类型    | 必填 | 默认值 |
|:-----|:--------|-------|----|:----|
| list | 需要重排的数组 | array | 是  |     |

用例

```php
$list = [
    ['id' => 1, 'name' => 'name1', 'other_key' => '1', 'value' => 'value1'],
    ['name' => 'name2', 'id' => 2, 'value' => 'value2'],
    ['value' => 'value3', 'id' => 3, 'name' => 'name3'],
];

$result = reorderRowKey($list);
```