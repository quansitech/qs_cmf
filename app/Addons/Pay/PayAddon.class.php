<?php
namespace Addons\Pay;
use Addons\Addon;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WeixinLoginAddon
 *
 * @author 英乐
 */
class PayAddon extends Addon{
    //put your code here
    
    public $info = array(
        'name' => 'Pay',
        'title' => '网上支付',
        'description' => '网上支付模块',
        'status' => 1,
        'author' => 'tider',
        'version' => '0.1'
    );
    
    public function install(){ 
        return true;
    }
    
    public function uninstall(){
        return true;
    }
    
}
