<?php
namespace Lara\Tests\Featrue;

use Illuminate\Support\Facades\DB;
use Lara\Tests\DuskTestCase;

class LoginTest extends DuskTestCase {

    public function testAdminLogin(){
        $this->browse(function($browser){
            $user = DB::table('user')->find(1);

            $browser->visit("/admin/Public/login")
                ->waitFor('#login-box')
                ->type('uid', 'admin')
                ->type('pwd', 'Qs123!@#')
                ->press('button[type=submit]')
                // v15 后台为 Inertia + React，登录成功跳转 Dashboard，挂载点为 #app
                // （其 data-page JSON 的 layoutProps.userName 即登录用户名）
                ->waitFor('#app')
                ->assertPathIs('/Admin/Dashboard/index')
                // 经 script 读取 Inertia data-page 验证登录用户名
                ->assertScript(
                    "return JSON.parse(document.getElementById('app').dataset.page).props.layoutProps.userName",
                    'admin'
                );
        });
    }
}