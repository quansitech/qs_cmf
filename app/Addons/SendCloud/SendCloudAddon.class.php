<?php
namespace Addons\SendCloud;
use Addons\Addon;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class SendCloudAddon extends Addon{
    public $info = array(
        'name' => 'SendCloud',
        'title' => '搜狐sendcloud邮件群发',
        'description' => '搜狐sendcloud邮件群发',
        'status' => 1,
        'author' => 'tider',
        'version' => '0.1'
    );
    
    public function install(){
        $this->createHook('sendEmailBatch', '群发邮件');
    }
    
    public function uninstall(){
        $this->delHook('sendEmailBatch');
    }
    
    public function sendEmailOne(&$para){
        $config = $this->getConfig();
        
        $send_cloud = new \Util\SendCloud($config);
        if(isset($para['reply_to'])){
            $send_cloud->setReplyTo($para['reply_to']);
        }
        
        if(isset($para['from'])){
            $send_cloud->setFrom($para['from']);
        }
        
        if(isset($para['from_name'])){
            $send_cloud->setFromName($para['from_name']);
        }
    }
}
