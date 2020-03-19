<?php
namespace Lara\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Testing\TestCase;

class UserTest extends TestCase {

    public function testAdd(){

        $this->loginSuperAdmin();

        $content = $this->post('/admin/user/add', [
            'nick_name' => 'tider',
            'email' => 'tider@qq.com',
            'telephone' => '13800003021',
            'pwd' => '123456',
            'pwd1' => '123456',
            '__hash__' => $this->getTpToken('/admin/user/add', false)
        ]);

        $this->assertTrue(Str::contains($content, '新增成功'));

        $this->assertDatabaseHas('qs_user', [
            'nick_name' => 'tider',
            'email' => 'tider@qq.com',
            'telephone' => '13800003021'
        ]);
    }

    public function testDelete(){
        DB::insert("INSERT INTO `qs_user` (`id`, `nick_name`, `salt`, `pwd`, `email`, `telephone`, `register_date`, `status`, `last_login_time`, `last_login_ip`) VALUES
(2, 'tider', 275489, '7b082838e7b48377b2d158fa47c99857', 'tider@qq.com', '13800003021', 1560250480, 1, 0, '')");

        $this->loginSuperAdmin();

        $content = $this->get('/admin/user/delete/ids/2');

        $this->assertTrue(Str::contains($content, '删除成功'));

        $this->assertDatabaseMissing('qs_user', [
            'nick_name' => 'tider',
            'email' => 'tider@qq.com',
            'telephone' => '13800003021'
        ]);
    }
}