<?php
namespace Behaviors;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class AppInitBehavior extends \Think\Behavior{
    
    public function run(&$parm){
        //定义常量
        define('SITE_URL', $_SERVER['HTTP_HOST']);
        
        define('HTTP_PROTOCOL', $_SERVER[C('HTTP_PROTOCOL_KEY')]);

        //header安全
        header("X-XSS-Protection: 1; mode=block");
        header("X-Content-Type-Options: nosniff");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        
        // 处理队列配置
        $config = C('QUEUE');
        if ($config) {
            import('Common.Util.tp3-resque.autoload');
            // 初始化队列服务,使用database(1)
            \Resque::setBackend(['redis' => $config], 1);
            // 初始化缓存前缀
            if(isset($config['prefix']) && !empty($config['prefix']))
            \Resque\Redis::prefix($config['prefix']);

            \Resque\Event::listen('afterScheduleRun', function($args){
                $job_id = $args['job_id'];
                $job_desc = $args['job_desc'];
                $job = $args['job'];
                $param = $args['job_args'];

                $data['id'] = $job_id;
                $data['job'] = $job;
                $data['args'] = json_encode($param);
                $data['description'] = $job_desc;
                $data['status'] = \Gy_Library\DBCont::JOB_STATUS_WAITING;
                $data['create_date'] = time();
                $data['schedule'] = \Gy_Library\DBCont::YES_BOOL_STATUS;

                D('Queue')->add($data);
            });
        }
    }
}