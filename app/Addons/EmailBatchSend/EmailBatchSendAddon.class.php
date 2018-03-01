<?php
namespace Addons\EmailBatchSend;
use Addons\Addon;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EmailBatchSendAddon
 *
 * @author 英乐
 */
class EmailBatchSendAddon extends Addon{
    //put your code here
    
    public $info = array(
        'name' => 'EmailBatchSend',
        'title' => '邮件群发',
        'description' => '',
        'status' => 1,
        'author' => 'tider',
        'version' => '0.1'
    );
    
    public function install(){ 
        return true;
    }
    
    public function uninstall(){
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}weixin_login;");
        return true;
    }
}
