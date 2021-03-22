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
+ insertframe: 默认启用。用于插入```<iframe></iframe>```或```url```，可以编辑宽高，边框，是否允许滚动,对齐方式等属性,其他属性会被删除。

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

重新指定UE的JS CONFIG文件的路径
```php
//在Common/Conf/config.php中新增配置值
'CUSTOM_UEDITOR_JS_CONFIG' => __ROOT__ . '/Public/static/ueditor.config.js'  //注意必须加上__ROOT__，为了兼容根目录是网站子路径的情况
```


设置ue的option参数
```php
//如：想通过form.options来配置ue的toolbars参数
//组件会自动完成php数组--》js json对象的转换，并传入ue中
->addFormItem('content', 'ueditor', '内容', '', ['toolbars' => [['attachment']]])
```

自定义UE色板
```php
全局配置
1.先COPYueditor.config.js文件到项目路径，重新指定JS CONFIG路径
2.修改ueditor.config.js 的customColors配置项，第一行10色块为主题色块， 最后一行10色块为标准色块，可按照需要自行增删改里面的色值。


局部配置
1. 在Formbuilder设置formItem时，可传递customColors的设置，详细方法查看“设置ue的option参数”
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

#### 只读模式
可通过setReadOnly(true)方法设置form为只读模式，默认是关闭

只读模式需要组件支持

组件可通过传入的设置项获取是否只读模式，然后做相应的展示适配

如Text组件
```php
class Text implements FormType {

    public function build(array $form_type){
        $view = new View();
        $view->assign('form', $form_type);
        if($form_type['item_option']['read_only']){
            $content = $view->fetch(__DIR__ . '/text_read_only.html');
        }
        else{
            $content = $view->fetch(__DIR__ . '/text.html');
        }
        return $content;
    }
}
```

#### 扩展表单与按扭间的底部内容
通过addBottom向表单加入需要的html/js代码
```php
//TableBuilder 和 DividerBuilder 为 qscmf-antd-builder的模块功能
$table_builder = new TableBuilder();
$table_builder->addColumn([ 'title' => 'Name', 'dataIndex' => 'name']);
$table_builder->addColumn([ 'title' => 'Age', 'dataIndex' => 'age']);
$table_builder->addColumn([ 'title' => 'Address', 'dataIndex' => 'address']);
$table_builder->addRow(['key' => 1, 'name' => 'John Brown', 'age' => 32, 'address' => 'New York No. 1 Lake Park']);
$table_builder->addRow(['key' => 2, 'name' => 'Jim Green', 'age' => 42, 'address' => 'London No. 1 Lake Park']);
$table_builder->addRow(['key' => 3, 'name' => 'Joe Black', 'age' => 32, 'address' => 'Sidney No. 1 Lake Park']);

$formbuilder = new FormBuilder();
.
.
.

    $formbuilder->setReadOnly(true)
    ->addBottom((new DividerBuilder())->setTitle('筹款记录'))
    ->addBottom($table_builder)
    ->display();
```

#### setShowBtn
```blade
该方法用于设置是否展示按钮

参数
$is_show 是否展示，默认为true
```

#### display
```blade
该方法用于显示页面，支持获取输出页面的内容

参数
$render 是否输出页面的内容，默认为false
```

效果图
<img src='https://user-images.githubusercontent.com/1665649/85219268-f5699f00-b3d4-11ea-98a0-6192336872b8.png' />

#### district组件
```blade
省市区三级联动

支持自定义省市区数据源api，默认为Api/Area/getArea，自定义的api需注意返回的数据类型
```

```php
// 使用说明
// addFormItem第五个参数，传递自定义api，加上area_api_url
->addFormItem('city_id', 'district', '城市', '', ['area_api_url' => U('Api/Area/fetchLimitCity', '', '', true)]);
```

#### select2组件
```blade
下拉选择，支持模糊搜索

支持多选
```

```php
// 使用说明
// addFormItem第七个参数，传递extra_attr，值包括multiple="multiple"
$project_info = [
    '41' => 'text1',
    '42' => 'text2',
    '43' => 'text3',
];
->addFormItem('project_id','项目','select2', '', $project_info, '', 'multiple="multiple"');
```