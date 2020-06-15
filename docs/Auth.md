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

### 字段权限过滤机制
```blade
当系统存在多种不同类型的用户，特定字段只有部分用户有操作权限，可以添加虚拟节点为权限点，有该权限点的用户才可以操作该字段。
```

#### 场景模拟
```blade
如系统存在机构管理员OrgUser与书库点管理员LibraryUser，均可新增或者编辑书箱Box。
但是捐赠方company_id与冠名caption字段书库点管理员没有操作权限。

按照以前的做法需要检测登录的用户，然后针对不同类型用户分别做这些字段的显示和操作逻辑限制。

使用该机制可以解决这个需求。
```

#### 用法

+ 在Model类配置$_auth_node_column的值
```blade
字段说明

字段名
auth_node：权限点，格式为：模块.控制器.方法名，如：'admin.Box.allColumns'；多个值需使用数组，如：['admin.Box.allColumns','admin.Box.add','admin.Box.edit']
default：默认值

若auth_node存在多个值，则需要该用户拥有全部权限才可以操作该字段
```
```php
    // 在BoxModel配置需要权限过滤的字段，只有拥有该权限点的用户才可以操作字段
    
    protected $_auth_node_column = [
        'company_id' => ['auth_node' => 'admin.Box.allColumns'],
        'caption' => ['auth_node' => ['admin.Box.allColumns','admin.Box.add','admin.Box.edit'],'default' => 'quansitech']
    ];
```

+ 使用addFormItem设置表单并配置auth_node属性，具体规则参考FormBuilder的addFormItem方法
```php
    // 在构建新增或者编辑书箱表单时，设置auth_node属性
    // auth_node值应与$_auth_node_column对应字段的auth_node值一致
    
    ->addFormItem('company_id', 'select', '捐赠方', '', D('Company')->where(['status' => DBCont::NORMAL_STATUS])->getField('id,name'), '', '', ['admin.Box.allColumns'])
    ->addFormItem('caption', 'text', '冠名', '冠名长度不得超过10个字', '', '', '', ['admin.Box.allColumns','admin.Box.add','admin.Box.edit'])
```

+ 创建auth_node的节点