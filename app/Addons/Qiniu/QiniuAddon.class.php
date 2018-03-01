<?php
namespace Addons\Qiniu;
use Addons\Addon;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class QiniuAddon extends Addon{
    
    public $info = array(
        'name' => 'Qiniu',
        'title' => '七牛云存储',
        'description' => '七牛云存储',
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
    
    public function formBuilder($form){
        $config = $this->getConfig();
        $type_arr = explode('_', $form['type']);
        if(count($type_arr) != 3 || $type_arr[0] != 'Addons' || $type_arr[1] != 'Qiniu'){
            return false;
        }
        
        switch($type_arr[2]){
            case 'video':
                $qiniu = new \Addons\Qiniu\Util\Qiniu();
                $this->assign('form', $form);
                $this->assign('uptoken', $qiniu->uploadToken($config));
                $this->assign('domain', $config['domain']);
                $this->display(T('Addons://Qiniu@default/upload_video'));
                break;
        }
    }
}
