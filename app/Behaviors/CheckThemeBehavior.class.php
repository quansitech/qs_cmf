<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2013 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Behaviors;
use Think\Behavior;

// 初始化钩子信息
class CheckThemeBehavior extends Behavior {

    // 行为扩展的执行入口必须是run
    public function run(&$content){
        $mobile_detect = new \Common\Util\Mobile_Detect();
       if($mobile_detect->isMobile() && MODULE_NAME != 'Admin'){
           C('DEFAULT_THEME', 'mobile');
       }
       else{
           C('DEFAULT_THEME', 'default');
       }
    }
}