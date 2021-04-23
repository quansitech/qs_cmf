### 重置RBAC用户表和用户与用户组关联表
#### 用法
+ config.php配置INJECT_RBAC  
```blade
// key为标识字段，用户登录后存入session中
// user为RBAC对应的用户数据表（程序使用D函数）
// role_user为用户与用户组关联数据表（程序使用原生sql）
'INJECT_RBAC' => [
    ['key' => 'PUBLIC_USER_LOGIN_ID', 'user' => 'User', 'role_user' => 'qs_role_user']
]
```

+ 用户登录后使用cleanRbacKey清空key的session值再重置对应key的session值

+ 用户登出后使用cleanRbacKey清空key的session值

### 权限过滤机制
不同用户一般只能看到与自己相关的数据，该机制可以限制后台用户访问数据的权限，不用针对不同用户分别处理where表达式，降低开发难度。

#### 用法
+ 用户登录成功后设置AUTH_RULE_ID的session值；

+ 配置对应Model类的$_auth_ref_rule（若查询数据表存在别名，自动处理auth_ref_key为“别名.字段名”）

如某机构（全思小伙伴）只能查看其创建书库点的书箱，则：
```php
    // auth_ref_key是与用户关联的字段，即AUTH_RULE_ID的值
    // ref_path是该字段有关的数据表及其对应字段
    
    // 机构OrganizationModel的配置
    protected $_auth_ref_rule = array(
        'auth_ref_key' => 'id',
        'ref_path' => 'Organization.id'
    );
    
    // 书库点LibraryModel的配置
    protected $_auth_ref_rule = array(
        'auth_ref_key' => 'org_id',
        'ref_path' => 'Organization.id'
    );
    
    // 书箱BoxModel的配置
    protected $_auth_ref_rule = array(
        'auth_ref_key' => 'library_id',
        'ref_path' => 'Library.id'
    );
```

若不使用该机制，则需要根据登录用户来处理where表达式，会导致查询的层级越高，代码量就越多：
```php
    // 不使用该机制，机构查询书箱数据，则需要根据登录用户获取org_id，再根据org_id获取library_id，再根据library_id获取书箱id，最后根据书箱id找出书箱数据：
    if (session('?' . C('USER_AUTH_KEY')){
        $org_id = D('OrganizationUser')->where(['id' => session(C('USER_AUTH_KEY'))])->getField('org_id');
        !$org_id && $org_map['_string'] = "1=0";
        $org_id && $library_ids = D('Library')->where(['org_id' => $org_id])->getField('id', true);
        if ($library_ids){
            $box_ids = D('Box')->where(['library_id'=>['IN',$library_ids]]) -> getField('id',true);
            $box_ids && $org_map['id'] = ['IN', $box_ids];
            !$box_ids && $org_map['_string'] = "1=0";
        }else{
            $org_map['_string'] = "1=0";
        }
        $org_map && $map = array_merge($map, $org_map);
    }
    D('Box')->where($map)->select();
    
```

使用该机制后，机构查询书箱数据：
```php
    D('Box')->where($map)->select();
```

### 扩展权限过滤机制
如系统存在机构用户OrgUser与书库点管理员LibraryUser，有书库点Library数据分别与他们关联（org_id与id）时，对应的LibraryModel应该这样配置：
```php
// 对于OrgUser，书库点LibraryModel的配置
protected $_auth_ref_rule = array(
    'auth_ref_key' => 'org_id',
    'ref_path' => 'Organization.id'
);

// 对于LibraryUser，书库点LibraryModel的配置
protected $_auth_ref_rule = array(
    'auth_ref_key' => 'id',
    'ref_path' => 'Library.id'
);
```

显然之前的权限过滤机制不能满足需求。

当系统存在多种不同类型的用户，而这些用户与数据相关联的字段不一致，扩展后可以根据不同类型的用户配置不同的权限过滤。

#### 用法
+ 配置对应Model类的$_auth_ref_rule，自定义不同用户类型的权限过滤

```php
    // 机构OrganizationModel的配置
    protected $_auth_ref_rule = array(
        'auth_ref_key' => 'id',
        'ref_path' => 'Organization.id'
    );
    
    // 用户类型org为机构
    // 用户类型library为书库点管理员

    // 书库点LibraryModel的配置
    protected $_auth_ref_rule = array(
        'org' => [
            'auth_ref_key' => 'org_id',
            'ref_path' => 'Organization.id'
        ],
        'library' => [
            'auth_ref_key' => 'id',
            'ref_path' => 'Library.id'
        ]
    );
```

+ 登录方法需先清空再设置“AUTH_RULE_ID”、“AUTH_ROLE_TYPE”对应的值

```php

    // 用户类型为“library”的登录方法
    public function libraryUserLogin($name,$pwd){
        // 省略登录逻辑处理
        ……
        ……
        ……

        // 登录成功后
        if ($r !== false){
            cleanRbacKey();
                   
            session('LIBRARY_USER_LOGIN_ID',$ent['id']);            
            session(C('USER_AUTH_KEY'), $ent['id']);
            
            \Qscmf\Core\AuthChain::setAuthFilterKey($ent['library_id'], 'library');

            session('ADMIN_LOGIN', true);
            session('HOME_LOGIN', null);
            sysLogs('书库点管理员后台登录');

            return true;
        }else{
            return false;
        }
    }
    
    // 用户类型为“org”的登录方法
    public function adminLogin($user_name, $pwd){
        // 省略登录逻辑处理
        ……
        ……
        ……
        
        // 登录成功后            
        if (!$ent){
            return false;
        }             
        cleanRbacKey();

        // 设置超级管理员权限
        if ($ent['id'] == C('USER_AUTH_ADMINID')) {
            session(C('ADMIN_AUTH_KEY'), true);
        } else {
            session(C('ADMIN_AUTH_KEY'), false);
        }        
        
        session('ORG_USER_LOGIN_ID', $ent['id']);      
        session(C('USER_AUTH_KEY'), $ent['id']);
        
        \Qscmf\Core\AuthChain::setAuthFilterKey($ent['company_id'], 'org');
        
        session('ADMIN_LOGIN', true);
        session('HOME_LOGIN', null);
        sysLogs($ent['company_id'] ? '机构后台登录' : '平台用户后台登录');

        return true;
    }
    
```
+ 登出方法需要使用函数“cleanRbacKey”、“cleanAuthFilterKey”清空对应的值

```php
    // 用户类型为“org”的登出方法
    public function sso_out(){
        if (isAdminLogin()) {
            cleanRbacKey();
            
            \Qscmf\Core\AuthChain::cleanAuthFilterKey();

            session(C('ADMIN_AUTH_KEY'), null);
            session(C('USER_AUTH_KEY'), null);
            session('ADMIN_LOGIN', null);
            sysLogs('后台登出');
        }
    }
    
    // 用户类型为“library”的登出方法
    public function libraryUserLogout(){
        if (isAdminLogin()) {
            cleanRbacKey();
            
            \Qscmf\Core\AuthChain::cleanAuthFilterKey();

            session(C('ADMIN_AUTH_KEY'), null);
            session(C('USER_AUTH_KEY'), null);
            session('ADMIN_LOGIN', null);
            sysLogs('后台登出');
        }
        
        $this->redirect('Public/libraryUserLogin');
    }
```

### 开启前台过滤机制
可以config文件中将 'FRONT_AUTH_FILTER' 设置为true
```blade
'FRONT_AUTH_FILTER'=>true,
```


### 权限过滤机制支持配置若不存在关联数据则取消过滤
不存在关联数据时，默认返回空。使用此功能可以实现用户存在关联数据则只能查看关联的数据，若不存在关联数据则可以查看所有数据。

#### 用法
+ 配置对应Model类属性$_auth_ref_rule的not_exists_then_ignore，其值为true
```blade
该值应设置在需要获取的关联数据主表对应的Model类，如用户（UserModel类）与地区（AreaModel类）关联，需要获取地区的数据，应在AreaModel类设置该值为true。
```

```php
// 设置该值为true
// AreaModel类的配置
protected $_auth_ref_rule = array(
    'auth_ref_key' => 'id',
    'ref_path' => 'UserArea.city_id',
    'not_exists_then_ignore' => true
);
```


### 权限过滤机制支持使用回调函数修改关联数据
权限过滤的关联数据为回调函数的结果。

#### 用法
+ 定义回调函数

+ 配置对应Model类属性$_auth_ref_rule的auth_ref_value_callback
```blade
auth_ref_value_callback的值为索引数组。 
数组第一个元素为被调用的回调函数，若为公共函数，则为字符串；若为某个类的方法，则为数组，如[类名，方法名]；
数组之后的元素是要被传入回调函数的参数。

回调函数接收关联数据的参数位置没有硬性规定，可以根据实际情况使用占位符__auth_ref_value__，程序执行时会根据ref_path的设置，获取关联字段的实际值传给回调函数，注意该值为数组。

该值应设置在需要获取的关联数据主表对应的Model类，如用户（UserModel类）与地区（AreaModel类）关联，需要获取地区的数据，应在AreaModel类设置该值。
```

```php
// AreaModel类的配置
protected $_auth_ref_rule = array(
    'auth_ref_key' => 'id',
    'ref_path' => 'UserArea.city_id',
    'auth_ref_value_callback' => ['callback_fun_name','__auth_ref_value__','param2'],
);
```

#### 场景举例
##### 若关联数据为同类型的树状结构数据，例如省市区、商品的类目，关联数据应扩展为该节点的子树。

```blade
若用户A与省市区的关联数据为：广东省、湖南省，则该用户可以查看这些省份及其下属所有地区的数据；
若用户B没有关联地区数据，则该用户可以查看所有地区的数据。
```

```php
// 不使用权限过滤功能，则每次获取用户能查看的地区数据时，都需要过滤地区数据。
public function getArea(){
    $user_id = D('User')->getLoginUserId();
    $map = [];
    D('Area')->genWhereByUid($user_id, $map, 'id');
    
    $area = D('Area')->where($map)->select();
    return $area;
}

// AreaModel类
// 根据uid获取可以查看的地区数据
public function genWhereByUid($uid, &$map, $field){ 
    $city_id = D('UserArea')->where(['user_id'=>$uid])->getField('city_id', true);
    $all_city_id = $city_id ? getAllAreaIdsWithMultiPids($city_id) : null;
    if ($all_city_id){
        $map[$field] = ['IN', $all_city_id];
    }
}
```

```blade
若使用之前的权限过滤功能，用户A只能查看广东省、湖南省的数据，而属于广东省的广州市的数据会被过滤掉；
用户B可以查看的数据为空。

以下是使用此功能的步骤与效果。
```

+ 定义回调函数getFullAreaIdsWithMultiPids，实现根据多个地区数据，返回这些地区及其下属所有地区的数据

```php
function getAllAreaIdsWithMultiPids($city_ids, $model = 'AreaV', $max_level = 3, $need_exist = true, $cache = ''){
    // 根据多个地区id获取其下属的所有地区，具体算法省略
    $all_city_ids = [];
    foreach ($city_ids as $v){
    }

    return $all_city_ids;
}
```

+ 配置对应Model类属性$_auth_ref_rule，定义回调函数及其参数
```php
// 配置地区AreaModel类
// 方式一：使用公共函数作为回调函数
protected $_auth_ref_rule = array(
    'auth_ref_key' => 'id',
    'ref_path' => 'UserArea.city_id',
    'auth_ref_value_callback' => ['getAllAreaIdsWithMultiPids','__auth_ref_value__','AreaV',3,false],
    'not_exists_then_ignore' => true
);

// 方式二：使用某个类的方法作为回调函数
protected $_auth_ref_rule = array(
    'auth_ref_key' => 'id',
    'ref_path' => 'UserArea.city_id',
    'auth_ref_value_callback' => [[FullAreaModel::class,'getAllAreaIdsWithMultiPids'],'__auth_ref_value__','AreaV',3,false],
    'not_exists_then_ignore' => true
);

// 配置UserAreaModel类 
protected $_auth_ref_rule = array(
    'auth_ref_key' => 'user_id',
    'ref_path' => 'UserArea.city_id'
);
```

```php
// 只需要正确配置权限链使用此功能就可以实现需求，不再需要额外代码去过滤用户的关联地区数据。
public function getArea(){
    $area = D('Area')->select();
    return $area;
}
```

### 自定义Session类用于处理权限过滤使用的标识值
```blade
若使用权限链功能，就需要根据实际使用情况设置AUTH_RULE_ID、INJECT_RBAC等值，这些标识值默认使用公共函数session管理。

但是在前后端分离开发方式的系统，不适用公共函数session。

可以通过\Qscmf\Core\AuthChain类的registerSessionCls方法自定义Session类，处理标识值。
```

```php
// 建议使用\Qscmf\Core\AuthChain类的get方法获取标识值
// 如获取AUTH_RULE_ID、AUTH_ROLE_TYPE的值

\Qscmf\Core\AuthChain::get(AuthChain::AUTH_RULE_ID);
\Qscmf\Core\AuthChain::get(AuthChain::AUTH_ROLE_TYPE);

```

#### 用法
+ 定义Session类，实现接口Qscmf/Core/AuthChain/IAuthChainSession
```blade
默认为\Qscmf\Core\AuthChain\CommonAuthChainSession类，使用公共函数session管理。
```

```php
class CusAuthChainSession implements AuthChain\IAuthChainSession
{
     public function set($key, $value)
    {
        CusSession::set($key, $value);
    }

    public function get($key){
        return CusSession::get($key);
    }
    
    public function clear($key)
    {
        $this->set($key,null);
    }

}
```

+ 在app_init行为中加入注册该Session类
```php
class AppInitBehavior extends \Think\Behavior{

    public function run(&$parm){
        // 其它逻辑省略...

        AuthChain::registerSessionCls(CusAuthChainSession::class);

    }
}
```