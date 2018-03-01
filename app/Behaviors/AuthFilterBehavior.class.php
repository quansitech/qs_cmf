<?php
namespace Behaviors;
use Org\Util\Rbac;

class AuthFilterBehavior extends \Think\Behavior{
    
    private $auth_bundle_regex = '/<(\w+) [^<]+?(auth_bundle=(?:\'|")([^<]+?)(?:\'|"))[^<]*?(\/>|>)/';
    private $tag_regex_tpl = '/<{tag}[^<]*?>(?:(?:(?!<div).)*?)<\/{tag}>/';
    
    //查找auth_bundle，如找到则提取出tag名称，及auth_bundle的值,放入解析函数
    public function run(&$param){
        $content = preg_replace("/[\t\n\r]+/","",$param);
    	while(preg_match($this->regex,$content,$matches)){
            
            $content = $this->analyzeAuthValue($content, $matches);
            
        }
    }
    
    //解析auth_bundle的值,并返回替换后的$content
    private function analyzeAuthValue($content, $matches){
        $auth = $matches[3];
        
        if($this->checkAuth($auth)){
            return str_replace($matches[2], '', $content);
        }
        else{
            $tag = $matches[1];
            
            $tag_regex = str_replace('{tag}', $tag, $this->tag_regex_tpl);
            
            while(preg_match($tag_regex, $content, $tag_matches)){
                
            }
        }
    }
    
    private function filterNestedTag($content ){
        
    }
    
    //检查用户有无该权限, 有则返回true, 没有则返回false;
    private function checkAuth($auth){
        $auth_id = session(C('USER_AUTH_KEY'));
        if(!isSession($auth_id)){
            return false;
        }
        
        list($module_name, $controller_name, $action_name) = split('.', $auth);
        
        $access_list = Rbac::getAccessList($auth_id);
        if(isset($access_list[strtoupper($module_name)][strtoupper($controller_name)][strtoupper($action_name)])){
            return true;
        }
        else{
            return false;
        }
    }
    
    
}

