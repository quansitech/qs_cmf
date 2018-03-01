<?php

namespace Gy_Library;

class GyBuild extends \Think\Build{
    
    static protected $controller = '<?php
namespace [MODULE]\Controller;

class [CONTROLLER]Controller extends [PARENT_CONTROLLER] {

}';
    
    // 创建控制器类
    static public function buildController($module,$controller='Index', $parent_controller='\Gy_Library\GyController') {
        $file   =   APP_PATH.$module.'/Controller/'.$controller.'Controller'.EXT;
        if(!is_file($file)){
            $content = str_replace(array('[MODULE]','[CONTROLLER]', '[PARENT_CONTROLLER]'),array($module,$controller, $parent_controller),self::$controller);
            if(!C('APP_USE_NAMESPACE')){
                $content    =   preg_replace('/namespace\s(.*?);/','',$content,1);
            }
            file_put_contents($file,$content);
            
        }
    }
}
