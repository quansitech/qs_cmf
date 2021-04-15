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

### 权限过滤机制支持使用回调函数处理关联数据
目前的权限过滤功能不支持处理父子级关系的数据，例如用户与父级数据关联，那么他应该可以看到这个父级以及其子级的数据。

可以通过该功能，定义回调处理需要过滤的关联数据。

```blade
如系统某用户关联了多个省级地区，那么他只可以查看这些省份所有地区的数据；但是如果该用户没有关联地区数据，则可以查看所有地区的数据。
该需求可以通过 此功能以及公共函数：getFullAreaIdsWithMultiPids 实现。
```

#### 用法
+ 配置对应Model类属性$_auth_ref_rule的auth_ref_value_callback，定义回调函数及其参数

```php
// 使用公共函数作为回调函数
// __id__为占位符，执行时会将关联的实际值传给回调函数，注意该值为数组
protected $_auth_ref_rule = array(
    'auth_ref_key' => 'id',
    'ref_path' => 'UserArea.city_id',
    'auth_ref_value_callback' => ['getFullAreaIdsWithMultiPids','__id__'],
);

// 使用某个类的方法作为回调函数
// __id__为占位符，执行时会将关联的实际值传给回调函数，注意该值为数组
protected $_auth_ref_rule = array(
    'auth_ref_key' => 'id',
    'ref_path' => 'UserArea.city_id',
    'ref_callback' => [[UserAreaModel::class,'getCityId'],'__id__'],
    'not_exists_then_ignore' => true
);

public function getCityId($id){
    return $id;
}
```

+ 配置对应Model类属性$_auth_ref_rule的not_exists_then_ignore，设置该值为true时，可以实现找不到关联数据则不过滤
```blade
目前的权限过滤是只能查看关联的数据，当这种关联关系为限制的性质时，可以将该值设置为true，意为解除这种限制，取消过滤。
```
