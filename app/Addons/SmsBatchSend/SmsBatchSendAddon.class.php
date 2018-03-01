<?php
namespace Addons\SmsBatchSend;
use Addons\Addon;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SmsBatchSendAddon
 *
 * @author 英乐
 */
class SmsBatchSendAddon extends Addon{
    //put your code here
    public $info = array(
        'name' => 'SmsBatchSend',
        'title' => '短信群发',
        'description' => '短信群发',
        'status' => 1,
        'author' => 'tider',
        'version' => '0.1'
    );
    
    public function install(){ 
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}sms_log_h;");
        $model->execute("CREATE TABLE IF NOT EXISTS {$prefix}sms_log_h (`id` int(11) NOT NULL primary key auto_increment,`body` varchar(2000) NOT NULL,`create_user` int(11) NOT NULL,`create_date` int(11) NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        
        $model->execute("DROP TABLE IF EXISTS {$prefix}sms_log_d;");
        $model->execute("CREATE TABLE IF NOT EXISTS {$prefix}sms_log_d (`id` int(11) NOT NULL primary key auto_increment,`hid` int(11) NOT NULL,`sid` bigint(20) NOT NULL,`mobile` varchar(50) NOT NULL,`status` varchar(50) NOT NULL,`error_msg` varchar(500) NOT NULL,`send_date` int(11) NOT NULL,`rely` varchar(2000) not null) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        
        $model->execute("DROP TABLE IF EXISTS {$prefix}sms_menu_config;");
        $model->execute("CREATE TABLE IF NOT EXISTS {$prefix}sms_menu_config (`id` int(11) NOT NULL primary key AUTO_INCREMENT,`kname` varchar(50) NOT NULL,`value` varchar(50) not null) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        
        $menu_data['title'] = '短信群发';
        $menu_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $menu_data['sort'] = 998;
        $menu_data['type'] = 'backend_menu';
        $menu_data['icon'] = 'fa-folder';
        $menu_data['level'] = 1;
        $menu_id = D('Menu')->add($menu_data);
        
        $controller_data['name'] = 'SmsBatchSend';
        $controller_data['title'] = '短信群发';
        $controller_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $controller_data['sort'] = 1;
        $controller_data['pid'] = 1;
        $controller_data['level'] = \Gy_Library\DBCont::LEVEL_CONTROLLER;
        
        $controller_id = D('Node')->add($controller_data);
        
        $send_action_data['name'] = 'sendSms';
        $send_action_data['title'] = '短信群发';
        $send_action_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $send_action_data['pid'] = $controller_id;
        $send_action_data['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
        $send_action_data['menu_id'] = $menu_id;
        $send_action_data['url'] = addons_url('SmsBatchSend://SmsBatch/sendSms');
        $send_id = D('Node')->add($send_action_data);
        
        $log_action_data['name'] = 'smsLog';
        $log_action_data['title'] = '发送日志';
        $log_action_data['status'] = \Gy_Library\DBCont::NORMAL_STATUS;
        $log_action_data['pid'] = $controller_id;
        $log_action_data['menu_id'] = $menu_id;
        $log_action_data['level'] = \Gy_Library\DBCont::LEVEL_ACTION;
        $log_action_data['url'] = addons_url('SmsBatchSend://SmsBatch/smsLog');
        $log_id = D('Node')->add($log_action_data);
        
        $config_data['kname'] = 'menu_id';
        $config_data['value'] = $menu_id;
        M('SmsMenuConfig')->add($config_data);
        
        $config_data['kname'] = 'controller_id';
        $config_data['value'] = $controller_id;
        M('SmsMenuConfig')->add($config_data);
        
        $config_data['kname'] = 'send_id';
        $config_data['value'] = $send_id;
        M('SmsMenuConfig')->add($config_data);
        
        $config_data['kname'] = 'log_id';
        $config_data['value'] = $log_id;
        M('SmsMenuConfig')->add($config_data);
        
        return true;
    }
    
    public function uninstall(){
        $prefix = C("DB_PREFIX");
        $model = D();
        $model->execute("DROP TABLE IF EXISTS {$prefix}sms_log_h;");
        $menu_id = M('SmsMenuConfig')->where(array('kname' => 'menu_id'))->getField('value');
        $controller_id = M('SmsMenuConfig')->where(array('kname' => 'controller_id'))->getField('value');
        D('Menu')->where(array('id' => $menu_id))->delete();
        D('Node')->where(array('pid' => $controller_id))->delete();
        D('Node')->where(array('id' => $controller_id))->delete();

        $model->execute("DROP TABLE IF EXISTS {$prefix}sms_log_d;");
        $model->execute("DROP TABLE IF EXISTS {$prefix}sms_menu_config;");

        return true;
    }
}
