<?php
namespace Home\Controller;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class IndexController extends \Gy_Library\GyController{

    public function index(){
        $this->display();
    }

    public function errorDemo(){
        if(IS_POST){
            flashError('发生了错误');
            redirect(U('errorDemo'));
        }
        $this->display();
    }

    public function img(){
        $f = file_get_contents("https://csh-pub-resp.oss-cn-shenzhen.aliyuncs.com/Uploads/image/20170810/598c0fb49075e.jpg");
        $file_name = '/tmp/' . guid();
        $file = file_put_contents($file_name, $f);
        show_bug($file);
        show_bug($file_name);
        $r = getimagesize($file_name);
        show_bug($r);
    }

}
