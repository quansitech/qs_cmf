<?php

namespace Common\Util;
use Org\Util\Rbac;
use Think\Db;

class GyRbac extends Rbac{
    
    //重写权限认证的过滤器方法
    static public function AccessDecision($appName=MODULE_NAME, $controllerName=CONTROLLER_NAME, $actionName=ACTION_NAME) {
        
        \Think\Hook::listen('before_auth');

        //检查是否需要认证
        if(self::gyCheckAccess($appName, $controllerName, $actionName)) {
            //存在认证识别号，则进行进一步的访问决策
            $accessGuid   =   md5(strtoupper($appName).strtoupper($controllerName).strtoupper($actionName));
            if(empty($_SESSION[C('ADMIN_AUTH_KEY')])) {
                if(C('USER_AUTH_TYPE')==2) {
                    //加强验证和即时验证模式 更加安全 后台权限修改可以即时生效
                    //通过数据库进行访问检查
                    $accessList = self::getAccessList($_SESSION[C('USER_AUTH_KEY')]);
                }else {
                    // 如果是管理员或者当前操作已经认证过，无需再次认证
                    if( $_SESSION[$accessGuid]) {
                        return true;
                    }
                    //登录验证模式，比较登录后保存的权限访问列表
                    $accessList = $_SESSION['_ACCESS_LIST'];
                }
                //判断是否为组件化模式，如果是，验证其全模块名
                if(!isset($accessList[strtoupper($appName)][strtoupper($controllerName)][strtoupper($actionName)])) {
                    $_SESSION[$accessGuid]  =   false;
                    return false;
                }
                else {
                    $_SESSION[$accessGuid]	=	true;
                }
            }else{
                //管理员无需认证
				return true;
			}
        }
        return true;
    }
    
    static function gyCheckAccess($appName=MODULE_NAME, $controllerName=CONTROLLER_NAME, $actionName=ACTION_NAME) {
        $node_model = D('Node');
        $map['name'] = $appName;
        $map['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $map['level'] = \Gy_Library\DBCont::LEVEL_MODULE;
        $node = $node_model->getNode($map);
        
        //不存在该权限点，则无需权限控制
        if(is_null($node)){
            return false;
        }
        
        $map['name'] = $controllerName;
        $map['pid'] = $node['id'];
        $map['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $map['level'] = \Gy_Library\DBCont::LEVEL_CONTROLLER;
        $node = $node_model->getNode($map);
         if(is_null($node)){
            return false;
        }
        
        $map['name'] = $actionName;
        $map['pid'] = $node['id'];
        $map['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $map['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
        $node = $node_model->getNode($map);
         if(is_null($node)){
            return false;
        }
        
        return true;
    }
    
    //检查用户有无node_id的接入权限
    //return  true代表有   false代表无
    static function checkAccessNodeId($auth_id, $node_id){
        //超级管理员拥有所有权限
        if(!empty($_SESSION[C('ADMIN_AUTH_KEY')])){
            
            return true;
        }
        
        $db     =   Db::getInstance(C('RBAC_DB_DSN'));
        $table = array('role'=>C('RBAC_ROLE_TABLE'),'user'=>C('RBAC_USER_TABLE'),'access'=>C('RBAC_ACCESS_TABLE'),'node'=>C('RBAC_NODE_TABLE'));
        
        $sql    =   "SELECT access.* FROM ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$auth_id}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.status=1 and node.id='{$node_id}'";
        $apps =   $db->query($sql);
        
        if(count($apps) > 0){
            return true;
        }
        else{
            return false;
        }
    }
    
    
    static function checkAccess() {
        
    }
}
