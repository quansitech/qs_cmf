<?php

namespace Admin\Controller;
use Gy_Library\GyController;
use Testing\TestCase;

class DashboardController extends GyController{
    
    public function index(){

        $test = new TestCase();
        show_bug($test);
        $this->assign('meta_title','网站概况');
        $this->display();
    }
}