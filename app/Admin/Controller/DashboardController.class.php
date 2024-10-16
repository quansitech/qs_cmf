<?php

namespace Admin\Controller;
use Gy_Library\GyController;
use Qscmf\Lib\Inertia\Inertia;

class DashboardController extends GyController{
    
    public function index(){
        if (C('ANTD_ADMIN_BUILDER_ENABLE')) {
            Inertia::getInstance()->share('layoutProps.metaTitle', '网站概况');
            Inertia::getInstance()->render('Dashboard/Index', [

            ]);
            return;
        }

        $this->assign('meta_title','网站概况');
        $this->display();
    }
}