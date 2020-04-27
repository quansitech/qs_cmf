## FormBuilder

#### 事件
+ startHandlePostData   
确定按钮会监听该事件类型，可传递一个按钮描述。触发该事件后确定按钮会无效，描述会改成传递的字符串。
+ endHandlePostData  
确定按钮会监听该事件类型，触发该事件，确定按钮会重新生效，按钮描述会恢复。

#### ueditor
设置上传文件（或抓取远程图）的url前缀，和url后缀
```php
//addFormItem第七个参数，传递指定的上传处理地址，加上url_prefix参数和url_suffix
//拼接出的url结果： url_prefix . url原来的相对路径. url_suffix
->addFormItem('desc', 'ueditor', '商家简介', '', '', '', 'data-url="/Public/libs/ueditor/php/controller.php?url_prefix=prefix地址&url_suffix=后缀"')

//场景举例：
//某些管理员在上传富文本图片时，会上传一张非常大的图片，这样会导致用户访问该页面异常缓慢
//这时可以利用url_prefix配合imageproxy做到自动降低图片大小，降低图片占用的网络带宽

$url_prefix = U('/ip/q90', '', false, true) . '/' . U('/', '', false, true);
//url_prefix = http://域名/ip/q90/http://域名/图片地址
->addFormItem('desc', 'ueditor', '商家简介', '', '', '', 'data-url="/Public/libs/ueditor/php/controller.php?url_prefix=' . $url_prefix . '"')
```
+ insertframe: 默认启用。用于插入```<iframe></iframe>```代码，不能插入url，可以编辑宽高，边框，是否允许滚动,对齐方式等属性,其他属性会被删除。

使用oss作为文件存储服务
```php
//addFormItem第七个参数，传递指定的上传处理地址, oss设为1表示开始oss上传处理，type为指定的上传配置类型
->addFormItem('content', 'ueditor', '正文内容','', '','','data-url="/Public/libs/ueditor/php/controller.php?oss=1&type=image"')
```

复制外链文章时，强制要求抓取外链图片至本地，未抓取完会显示loadding图片(默认也会抓取外联图片，但如果未等全部抓取完就保存，此时图片还是外链)
```php
//addFormItem第七个参数，设置data-forcecatchremote="true"
->addFormItem('desc', 'ueditor', '商家简介', '', '', '', 'data-forcecatchremote="true"')
```

设置ue的option参数
```php
//如：想通过form.options来配置ue的toolbars参数
//组件会自动完成php数组--》js json对象的转换，并传入ue中
->addFormItem('content', 'ueditor', '内容', '', ['toolbars' => [['attachment']]])
```

自定义上传config设置

```blade
在app/Common/Conf 下新增ueditor_config.json或者ueditor_config.php(返回数组)，该文件将会替换掉默认的config.json。如有客制化config.json的需求，定制该文件即可。
```

#### addFormItem
```blade
该方法用于加入一个表单项

参数
$name item名
$type item类型(取值参考系统配置FORM_ITEM_TYPE)
$title item标题
$tip item提示说明
$options item options
$extra_class item项额外样式，如使用hidden则隐藏item
$extra_attr item项额外属性
$auth_node item权限点，需要先添加该节点，若该用户无此权限则unset该item；格式为：模块.控制器.方法名，如：['admin.Box.allColumns']

若auth_node存在多个值，支持配置不同逻辑（logic值为and或者or）判断是否显示该item，默认为and：
and：用户拥有全部权限则显示该item，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'and']
or：用户一个权限都没有则隐藏该item，格式为：
['node' => ['模块.控制器.方法名','模块.控制器.方法名'], 'logic' => 'or']

```
