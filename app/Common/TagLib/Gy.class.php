<?php

namespace Common\TagLib;
use Think\Template\TagLib;
use Common\Util\GyRbac;

class Gy extends TagLib{
    
    protected $tags   =  array(
            'auth'    =>  array('attr'=>'node','level'=>3),
            'nav'      =>  array('attr' => 'pid, type, name', 'close' =>1),
        );
    
    /**
     * auth标签解析
     * 如果某个用户有该权限，就输出内容
     * 格式： <auth node="" >content</auth>
     * @param array $tag 标签属性
     * @param string $content  标签内容
     * @return string
     */
    public function _auth($tag,$content) {
        $node       =   $tag['node'];
        $parseStr   = '<?php $result = verifyAuthNode("' . $node . '");';
        $parseStr   .=   ' if($result): ?>'.$content.'<?php endif; ?>';
        return $parseStr;
    }
    
    /* 导航列表 */
    public function _nav($tag, $content){
        $pid  = empty($tag['pid']) ? 0 : $tag['pid'];
        $type   =   empty($tag['type'])? '' : $tag['type'];
        $parse  = $parse   = '<?php ';
        $parse .= '$__NAV__ = getMenuTree(' . $pid . ', "' . $type .'");';
        $parse .= '?><volist name="__NAV__" id="'. $tag['name'] .'">';
        $parse .= $content;
        $parse .= '</volist>';
        return $parse;
    }
}

