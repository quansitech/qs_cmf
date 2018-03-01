<?php

namespace Admin\Widget;
use Think\Controller;

class AuthCheckWidget extends Controller{
    
    private $_controller_public_methods;
    
    public function index($module){
        echo ob_get_clean();
        
        $this->_controller_public_methods = $this->_getControllerPublicMethods();
        
        flushWebContent('开始检测 ' . $module . '权限点<br /><br />');
        
        flushWebContent('脱离权限控制的访问入口如下：<br /><br />');
        
        $files = array();
        searchDir(APP_PATH . ucfirst($module) . '/' . C('DEFAULT_C_LAYER'), $files);
        foreach($files as $file){
            if(preg_match('/(\w+)Controller.class.php$/', basename($file), $matches)){
                $controller_name = $matches[1];
                
                flushWebContent('<p style="color:red">' . $controller_name . ':</p>');
                
                $class = new \ReflectionClass(parse_res_name($controller_name, C('DEFAULT_C_LAYER'), C('CONTROLLER_LEVEL')));
                $methods = $class->getMethods();
                foreach($methods as $method){
                    $method_name = $method->getName();
                    if($method->isPublic() && $method->isUserDefined() && !in_array($method_name, $this->_controller_public_methods)){
                        if(!D('Node')->isExistsNode($module, $controller_name, $method_name)){
                            flushWebContent($method_name . '<br />');
                        }
                    }
                }
                flushWebContent('<br />');
            }
        }
    }
    
    private function _getControllerPublicMethods(){
        $class = new \ReflectionClass('\Think\Controller');
        $methods = $class->getMethods();
        $public_methods = array();
        foreach($methods as $method){
            $method_name = $method->getName();
            if($method->isPublic()){
                $public_methods[] = $method_name;
            }
        }
        return $public_methods;
    }
    
}