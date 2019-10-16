<?php

namespace Behaviors;


use Think\Behavior;

class ResetRbacBehavior extends Behavior
{
    public function run(&$params){
        if (env('RESET_RBAC') == true){
            $this->_injectRbac();
        }
    }

    private function _injectRbac(){
        $inject_rbac_arr = C('INJECT_RBAC');
        if ($inject_rbac_arr){
            array_map(function ($str){
                if (session("?{$str['key']}")){
                    C('USER_AUTH_MODEL', $str['user'], 'User');
                    C('RBAC_USER_TABLE', $str['role_user'], 'qs_role_user');
                }
            }, $inject_rbac_arr);
        }
    }

}