<?php
/**
 * Created by PhpStorm.
 * User: xh
 * Date: 2019/6/6
 * Time: 10:23
 */

namespace Common\Util;


class RedisLock
{
    protected $redis;
    protected $host;
    protected $port;
    protected $auth;
    protected $database_index;

    static private $_instance = array();

    private function __construct($config)
    {
        $this->redis = new \Redis();

        $this->host = $config['host'];
        $this->port = $config['port'] ? $config['port'] : 6379;
        $this->auth = $config['auth'];
        $this->database_index = $config['database_index'];

        $this->redis->connect($this->host, $this->port);
        $this->redis->auth($this->auth);
        $this->redis->select($this->database_index);
    }

    /**
     *
     * 取得类实例化对象
     *
     * @param $config   array
     * @return self   object
     */
    static function getInstance($config){
        $guid = to_guid_string($config);
        if (!(self::$_instance[$guid] instanceof self)){
            self::$_instance[$guid] = new self($config);
        }
        return self::$_instance[$guid];
    }

    /**
     *
     * 锁的状态
     *
     * @param $key      string  名称
     * @param $expire   int     过期时间 单位为秒
     * @return bool             锁成功返回true 锁失败返回false
     */
    public function lock($key, $expire){
        $is_lock = $this->redis->setnx($key, time()+$expire);
        if (!$is_lock){
            $current_expire = $this->redis->get($key);
            if ($this->isTimeExpired($current_expire)){
                $old_expire = $this->redis->getSet($key, time()+$expire);
                if ($this->isTimeExpired($old_expire)){
                    $is_lock = true;
                }
            }
        }
        return $is_lock;
    }

    /**
     *
     * 判断锁是否过期
     *
     * @param $expire   int 过期时间
     * @return bool         已过期返回true 未过期返回false
     */
    public function isTimeExpired($expire){
        return $expire < time();
    }

    /**
     *
     * 释放锁
     *
     * @param $key  string|array    名称
     * @return int                  释放锁的个数
     */
    public function unlock($key){
        return $this->redis->del($key);
    }

    /**
     *
     * 获取Redis原方法
     *
     * @return \Redis
     */
    public function getRedis(){
        return $this->redis;
    }

}