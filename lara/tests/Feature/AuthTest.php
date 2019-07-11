<?php
namespace Lara\Tests\Feature;

use Illuminate\Support\Str;
use Testing\TestCase;

class AuthTest extends TestCase {

    public function testLoginPage(){

            $content = $this->get('/Admin/Public/login');

            $this->assertTrue(Str::contains($content, '<div class="login-form-box" id="login-box">'));
    }

    public function testLogin(){
        $content = $this->post('/Admin/public/login', ['uid' => 'admin', 'pwd' => 'admin123']);

        $this->assertTrue(Str::contains($content, '登录成功'));
    }

    public function testLoginFail(){
        $content = $this->post('/Admin/public/login', ['uid' => 'admin', 'pwd' => 'admin']);

        $this->assertTrue(Str::contains($content, '用户名或密码错误'));
    }

    public function testDashboard(){

        $this->loginSuperAdmin();
        $content = $this->get('/Admin/Dashboard/index');

        $this->assertTrue(Str::contains($content, '<title>网站概况｜后台管理</title>'));
    }
}