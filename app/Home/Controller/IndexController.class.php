<?php
namespace Home\Controller;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Home\Lib\Foo;
use App\Models\User;

class IndexController extends \Gy_Library\GyController{

    public function index(){
        $this->display();
    }

    public function foo()
    {
        $this->inertia('Index/Foo', [
            'barUrl' => U('bar'),
        ]);
    }

    public function bar()
    {
        $this->inertia('Index/Bar', [
            'fooUrl' => U('foo'),
        ]);
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

    public function debug(){
        $user = new User();
        $r1 = $user->where('status', 0)->get();

        $r2 = User::where('status', 0)
            ->get();

        dd($r1, $r2);
    }
}
