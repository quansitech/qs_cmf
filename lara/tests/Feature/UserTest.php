<?php
namespace Lara\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lara\Tests\TestCase;

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

        $this->assertDatabaseHas('user', [
            'nick_name' => 'tider',
            'email' => 'tider@qq.com',
            'telephone' => '13800003021'
        ]);
    }

    public function testDelete(){
        DB::insert("INSERT INTO qs_user (id, nick_name, pwd, email, telephone, register_date, status, last_login_time, last_login_ip) OVERRIDING SYSTEM VALUE VALUES
(2, 'tider', '\$2y\$10\$uzQAmdyLqKe.XKjg74ibvufh5F2ERnazogAfE9K3rOw2UDCWqWqK', 'tider@qq.com', '13800003021', 1560250480, 1, 0, '')");

        $this->loginSuperAdmin();

        $content = $this->get('/admin/user/delete/ids/2');

        $this->assertTrue(Str::contains($content, '删除成功'));

        $this->assertDatabaseMissing('user', [
            'nick_name' => 'tider',
            'email' => 'tider@qq.com',
            'telephone' => '13800003021'
        ]);
    }
}