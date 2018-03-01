<?php
namespace Addons\Qiniu\Util;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Qiniu{
    
    public function __construct() {
        require_once 'autoload.php';
    }
    
    public function uploadToken($config){
        
        $auth = new \Qiniu\Auth($config['AK'], $config['SK']);
        
        $policy = array(
            'persistentOps' => $config['ops'],
            'persistentNotifyUrl' => $config['notifyUrl'],
            'persistentPipeline' => $config['pipeLine'],
        );
        
        return $auth->uploadToken($config['bucket'], null, 3600, $policy);
    }
}

