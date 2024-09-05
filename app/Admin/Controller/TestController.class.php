<?php

namespace Admin\Controller;

use Gy_Library\GyListController;

class TestController extends GyListController
{
    public function index()
    {
        $this->assign('meta_title', 'Test/Index');
        $this->inertia('Test/Index', [
            'test_url' => U('test'),
            'message' => '122111 message',
        ]);
    }

    public function test()
    {
        $this->inertia('Test/Test', [
            'index_url' => U('index'),
        ]);
    }
}