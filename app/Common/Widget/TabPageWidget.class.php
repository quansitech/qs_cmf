<?php

namespace Common\Widget;
use Think\Controller;

class TabPageWidget extends Controller{
    
    public function index($totalRows, $listRows=20,  $tab_page='', $tab=''){
        
        $para = I('get.');
        
        if($tab != ''){
            $pattern = '/' . $tab  . '-\d+/i';
            $para['tab'] = $tab;
            $para['tab_page'] = preg_replace($pattern, $tab . '-__PAGE__', $tab_page);
        }
        
        
        $page = new \Gy_Library\GyPage($totalRows, $listRows, $para);
        if($tab_page != '' && preg_match('/' . $tab  . '-(\d+)/i', $tab_page, $matches)){
            $p = $matches[1];
            $page->nowPage = $p;
            $page->unsetParameter('page');
        }
        
        $this->assign('pagination', $page->show());
        $this->display(T('Admin@common/pagination'));
    }
}