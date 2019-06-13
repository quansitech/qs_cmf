<?php
namespace Lara\Tests\Featrue;

use Illuminate\Support\Facades\DB;
use Lara\Tests\DuskTestCase;

class LoginTest extends DuskTestCase {

    public function testAdminLogin(){
        $this->browse(function($browser){
            $user = DB::table('qs_user')->find(1);

            $browser->visit("/admin/Public/login")
                ->waitFor('#login-box')
                ->type('uid', 'admin')
                ->type('pwd', 'admin123')
                ->press('button[type=submit]')
                ->waitFor('.user-menu')
                ->assertSeeIn('.user-menu', 'admin');
        });
    }
}