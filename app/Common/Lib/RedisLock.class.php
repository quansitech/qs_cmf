<?php
/**
 * Created by PhpStorm.
 * User: xh
 * Date: 2019/6/6
 * Time: 10:23
 */

namespace Common\Lib;


use Think\Cache;

class RedisLock
{
    protected $redis;

    public function __construct($config = [])
    {
        $this->redis = Cache::getInstance('redis', $config);
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
        $key = $this->redis->getOptions('prefix').$key;

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
        $key = $this->redis->getOptions('prefix').$key;

        return $this->redis->rm($key);
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