<?php
namespace Lara\Tests\Featrue;

use Illuminate\Support\Facades\DB;
use Lara\Tests\TestCase;

class LoginTest extends TestCase {

    public function testAdminLogin(){
        $this->browse(function($browser){
            $user = DB::table('qs_user')->find(1);

            $browser->visit("/admin/Public/login")
                ->waitFor('#login-box')
                ->type('uid', 'admin')
                ->type('pwd', 'kl201701')
                ->press('button[type=submit]')
                ->waitFor('.user-menu')
                ->assertSeeIn('.user-menu', 'admin');
        });
    }
}