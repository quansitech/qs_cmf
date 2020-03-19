<?php

namespace Admin\Controller;
use Gy_Library\GyController;

class DashboardController extends GyController{
    
    public function index(){

        $this->assign('meta_title','网站概况');
        $this->display();
    }
}