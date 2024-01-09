## Controller

#### redirect
```blade
Action跳转(URL重定向） 支持指定模块和延时跳转，若访问为ajax方式则返回json数据类型

string $url 跳转的URL表达式
array $params 其它URL参数，默认为空
integer $delay 延时跳转的时间，单位为秒，非ajax方式有效，默认为0
string $msg 跳转提示信息，默认为空
integer $status 状态信息，ajax方式有效，默认为0
boolean $ajax 是否为ajax方式，默认为false
```

```php
// 在Controller控制器中使用
// 若访问为ajax方式则返回json数据类型
$this->redirect('/admin/public/login', '', 0, '请先登录');
```

#### 自定义验证登录用户的状态的行为
```blade
当B系统的用户来源于A系统，可以使用自定义验证登录用户状态的行为，通过访问A系统提供的获取用户信息接口来判断用户状态。
```
+ 自定义行为
  ```php
  <?php

  namespace Behaviors;
  
  use Gy_Library\DBCont;
  
  class VerifyUserBehavior
  {
  
      public function run(){
          //非正常状态用户禁止登录后台
          $user_ent = fetchUserFromApi(session(C('USER_AUTH_KEY'))); // 通过接口获取用户信息
          if($user_ent['status'] != DBCont::NORMAL_STATUS){
              E('用户状态异常');
          }
      }
  
  }
  ```
+ 设置标签位 *verify_login_user* 
  ```blade
  设置 _overlay 为true，则应用的行为配置文件中的定义将覆盖系统的行为定义
  ```
  ```php
  'verify_login_user' => array('Behaviors\\VerifyUserBehavior','_overlay'=>true),   
  ```
