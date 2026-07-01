<?php
namespace Lara\Tests\Feature;

use Illuminate\Support\Str;
use Lara\Tests\TestCase;

class AuthTest extends TestCase {

    public function testLoginPage(){

            $content = $this->get('/Admin/Public/login');

            $this->assertTrue(Str::contains($content, '<div class="login-form-box" id="login-box">'));
    }

    public function testLogin(){
        $content = $this->post('/Admin/public/login', ['uid' => 'admin', 'pwd' => 'Qs123!@#']);

        $this->assertTrue(Str::contains($content, '登录成功'));
    }

    public function testLoginFail(){
        $content = $this->post('/Admin/public/login', ['uid' => 'admin', 'pwd' => 'admin']);

        // admin 用户存在但密码错误，v15 返回跳转提示页含「密码错误」
        $this->assertTrue(Str::contains($content, '密码错误'));
    }

    public function testDashboard(){

        $this->loginSuperAdmin();
        $content = $this->get('/Admin/Dashboard/index');

        // v15 后台为 Inertia + React 页面，断言 Inertia page 的 component 与 metaTitle
        $page = $this->inertiaPage($content);
        $this->assertNotNull($page, 'Dashboard 响应应为 Inertia 页面');
        $this->assertEquals('Dashboard/Index', $page['component']);
        $this->assertEquals('网站概况', $page['props']['layoutProps']['metaTitle']);
    }
}