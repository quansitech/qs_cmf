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

// 若修改了数据库连接的配置，要使全部模型生效，则需要同时关闭数据库连接和已经清除已经实例化的模型
D('','', \Qscmf\Lib\DBCont::CLOSE_TYPE_ALL);
```
