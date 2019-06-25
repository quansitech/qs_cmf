<?php
namespace Home\Controller;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Home\Lib\Foo;

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

    public function mock(){
        $foo = app()->make(Foo::class);
        echo $foo->say();
    }
}
