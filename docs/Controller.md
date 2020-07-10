## Controller

#### redirect
```blade
Action跳转(URL重定向） 支持指定模块和延时跳转，若访问为ajax方式则返回json数据类型

string $url 跳转的URL表达式
array $params 其它URL参数，默认为空
integer $delay 延时跳转的时间，单位为秒，非ajax方式有效，默认为0
string $msg 跳转提示信息，ajax方式有效，默认为空
integer $status 状态信息，ajax方式有效，默认为0
boolean $ajax 是否为ajax方式，默认为false
```

```php
// 在Controller控制器中使用
// 若访问为ajax方式则返回json数据类型
$this->redirect('/admin/public/login', '', 0, '请先登录');
```