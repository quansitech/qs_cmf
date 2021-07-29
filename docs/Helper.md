## 全局函数

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