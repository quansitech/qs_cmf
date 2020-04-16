#### 如何开发composer扩展包
以下说明需要具备composer包开发的相关基础

+ 新增provider
> ```php
> class CustomProvider implements \Bootstrap\Provider {
>  
>      public function register(){
>          // 相关注册代码
>      }
>  }
> ```

+ 注册迁移目录
> ```php
> class CustomProvider implements \Bootstrap\Provider, \Bootstrap\LaravelProvider {
>  
>     public function register(){
>         // 相关注册代码
>     }
> 
>     public function registerLara(){
>          //注册迁移目录代码
>     } 
> 
>     // register的方法是由TP发起的，TP相关的常量，函数可以使用
>     // registerLara是由laravel发起的，这里不要使用TP的内置方法，仅可执行TP的注册迁移函数
>  }
> ```

+ 框架提供的注册接口
> 1. registerController
>> + 说明：注册controller
>> + 参数：module_name 模块、 controller_name 控制器、controller_cls 需要映射的controller类名
>
> 2. registerListTopButton
>> + 说明: 注册列表按钮类型
>> + 参数：type 类型、 type_cls 继承\Qscmf\Builder\ButtonType\ButtonType的实现类
>
> 3. registerFormItem
>> + 说明：注册表单控件
>> + 参数：type 类型、 type_cls 继承\Qscmf\Builder\FormType\FormType的实现类
>
> 4. registerSymLink
>> + 说明: 注册软连接
>> + 参数：link_path 软连接绝对路径、 source_path 源绝对路径
>
> 5. registerListSearchType
>> + 说明: 注册列表搜索控件
>> + 参数: type 类型、 type_cls 继承\Qscmf\Builder\ListSearchType\ListSearchType的实现类
>
> 6. registerListRightButtonType
>> + 说明: 注册列表表格按钮
>> + 参数: type 类型、 type_cls 继承\Qscmf\Builder\ListRightButton\ListRightButton的实现类
>
> 7. registerMigration
>> + 说明：注册迁移文件目录
>> + 参数: paths 迁移文件存放的目录数组，只有一个目录时，可以只写一个字符串
>
> 8. registerHeadJs
>> + 说明: 在dashboard_layout head注册 js连接
>> + 参数: src js连接地址
>> +      async 是否异步加载（默认false）

+ 配置composer.json
> 在composer.json文件添加下面注册信息, 框架可通过该配置自动完成provider注册
> ```php
> "qscmf": {
>    "providers": [
>       #扩展包provider类名#
>    ]
> }
> ```

#### 扩展的自定义配置
读取扩展的自定义配置，可在扩展代码的任意位置通过该函数读取到用户定义的配置

配置文件: 放在根目录下的PackageConfig.php

packageConfig($package_name, $config)

参数：

package_name 扩展名

config 配置名 

举例:
```php
//PackageConfig.php
return [
     //quansitech/send-msg 扩展名
     //module 配置名
    'quansitech/send-msg' => [
        'module' => 'extendAdmin'
    ]
];
```


#### 如何开发表单控件
```php
use Think\View;
use Qscmf\Builder\FormType\FormType;

//实现 Qscmf\Builder\FormType\FormType 接口
//接口必须实现一个build函数，用于完成表单控件的渲染
class CustomType implements FormType{

    //form_type接收所有表单控件创建时需要的参数，如 name、title、tips、options等配置项
    public function build($form_type){
        $view = new View();
        $view->assign('form', $form_type);
        $content = $view->fetch(__DIR__ . '/custom.html');
        return $content;
    }
}
```

#### 如何开发列表按钮扩展
```php
use Illuminate\Support\Str;
use Qscmf\Builder\ButtonType\ButtonType;
use Think\View;

//必须继承\Qscmf\Builder\ButtonType\ButtonType抽象类
class CustomButton extends ButtonType{

    //实现抽象函数build，完成控件渲染
    // option接收所有按钮控件创建时需要的参数，如 type、attribute、tips、auth_node
    public function build(array $option){
        //默认配置值
        $my_attribute['type'] = 'custom';
        $my_attribute['title'] = '自定义类型';
        $my_attribute['target-form'] = 'ids';
        $my_attribute['class'] = 'btn btn-primary';
   
        //用户自定义覆盖默认配置
        if ($option['attribute'] && is_array($option['attribute'])) {
            $option['attribute'] = array_merge($my_attribute, $option['attribute']);
        }

        $gid = Str::uuid();
        $gid = str_repeat('-', '', $gid);
        $option['attribute']['id'] = 'modal-' . $gid;

        $view = new View();
        $view->assign('gid', $gid);
        //仅需渲染按钮外的内容，按钮的渲染由框架完成
        $content = $view->fetch(__DIR__ . '/custom.html');
        //返回渲染结果
        return <<<HTML
{$content}
HTML;
    }
}
```

#### 扩展列表
+ [阿里云视频点播vod](https://github.com/quansitech/qscmf-formitem-vod)
+ [七牛音视频组件](https://github.com/quansitech/qscmf-formitem-qiniu)
+ [文件批量导出并打包（zip）](https://github.com/quansitech/qscmf-topbutton-download)
+ [xlsx导出excel](https://github.com/quansitech/qscmf-topbutton-export)