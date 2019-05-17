<?php
namespace Behaviors;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class AppInitBehavior extends \Think\Behavior
{

    private function _setDBConfigWhileTesting(){

        //测试模式，数据库配置指向phpunit设置
        if(php_sapi_name() == 'cli-server' && file_exists(APP_DIR . '/../phpunit.xml')){
            //关闭伪静态才能正确获取PATH_INFO
            C('URL_HTML_SUFFIX', '');
            $content = file_get_contents(APP_DIR . '/../phpunit.xml');
            $phpunit_xml = simplexml_load_string($content);
            if($phpunit_xml) {
                foreach($phpunit_xml->php->env as $env){

                    switch($env['name']){
                        case 'DB_CONNECTION':
                            C('DB_TYPE', (string)$env['value']);
                            break;
                        case 'DB_HOST':
                            C('DB_HOST', (string)$env['value']);
                            break;
                        case 'DB_PORT':
                            C('DB_PORT', (string)$env['value']);
                            break;
                        case 'DB_DATABASE':
                            C('DB_NAME', (string)$env['value']);
                            break;
                        case 'DB_USERNAME':
                            C('DB_USER', (string)$env['value']);
                            break;
                        case 'DB_PASSWORD':
                            C('DB_PWD', (string)$env['value']);
                            break;
                    }
                }
            }
        }
    }
    
    public function run(&$parm){
        //定义常量
        define('SITE_URL', $_SERVER['HTTP_HOST']);
        
        define('HTTP_PROTOCOL', $_SERVER[C('HTTP_PROTOCOL_KEY')]);

        //header安全
        header("X-XSS-Protection: 1; mode=block");
        header("X-Content-Type-Options: nosniff");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

        $this->_setDBConfigWhileTesting();
        
        // 处理队列配置
        $config = C('QUEUE');
        if ($config) {
            import('Common.Util.tp3-resque.autoload');
            // 初始化队列服务,使用database(1)
            \Resque::setBackend(['redis' => $config], 1);
            // 初始化缓存前缀
            if(isset($config['prefix']) && !empty($config['prefix']))
            \Resque\RedisCluster::prefix($config['prefix']);

            \Resque\Event::listen('afterScheduleRun', function($args){
                $job_id = $args['job_id'];
                $job_desc = $args['job_desc'];
                $job = $args['job'];
                $param = $args['job_args'];
                $queue = $args['queue'];
                $schedule_id = $args['schedule_id'];

                $data['id'] = $job_id;
                $data['job'] = $job;
                $data['args'] = json_encode($param);
                $data['description'] = $job_desc;
                $data['status'] = \Gy_Library\DBCont::JOB_STATUS_WAITING;
                $data['create_date'] = time();
                $data['schedule'] = $schedule_id;
                $data['queue'] = $queue;

                D('Queue')->add($data);
            });

            \Resque\Event::listen('addSchedule', function($args){
                $id = $args['id'];
                $run_time = $args['run_time'];
                $preload = $args['preload'];

                $data['id'] = $id;
                $data['run_time'] = $run_time;
                $data['desc'] = $preload['desc'];
                $data['preload'] = json_encode($preload);
                $data['delete_status'] = \Gy_Library\DBCont::NO_BOOL_STATUS;
                $data['create_date'] = time();

                D('Schedule')->add($data);
            });

            \Resque\Event::listen('removeSchedule', function($args){
                $id = $args['id'];

                $ent = D("Schedule")->where(["id" => $id])->find();
                
                $ent["delete_status"] = \Gy_Library\DBCont::YES_BOOL_STATUS;
                D('Schedule')->save($ent);
            });
        }
    }
}