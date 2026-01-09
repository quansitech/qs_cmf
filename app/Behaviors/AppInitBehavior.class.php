<?php
namespace Behaviors;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Qscmf\Lib\DBCont;
use Qscmf\Lib\Tp3Resque\Resque;
use Qscmf\Lib\Tp3Resque\Resque\RedisCluster;
use Qscmf\Lib\Tp3Resque\Resque\Event;

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
        //header安全
        header("X-XSS-Protection: 1; mode=block");
        header("X-Content-Type-Options: nosniff");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

        $this->_setDBConfigWhileTesting();
        
    }
}