<?php

namespace Admin\Controller;

class IndexController extends \Gy_Library\GyController{
    
    public function index(){
        $this->redirect(C('USER_AUTH_GATEWAY'));
    }
}

