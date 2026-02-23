<?php

namespace backend\components;
use yii;

class MyRedis{

	private static $_instance = null;
    private  $redis;

	/**
     * @param string $host
     * @param int $post
     */
    public function __construct() {
        $this->redis = Yii::$app->redis;
        return $this->redis;
    }

    public static function getInstance(){
        if (null === self::$_instance) {
            $myredis = new MyRedis();

            self::$_instance = $myredis;
        }
        return self::$_instance;
    }



    /**
     * 设置值  构建一个字符串
     * @param string $key KEY名称
     * @param string $value  设置值
     * @param int $timeOut 时间  0表示无过期时间
     */
    public function set($key, $value, $timeOut=0) {
        if($this->redis == false) return false;
        $retRes = $this->redis->set($key, $value);
        if ($timeOut > 0){
            $this->redis->expire($key, $timeOut);
        }

        return $retRes;
    }

    /**
     * 通过key获取数据
     * @param string $key KEY名称
     */
    public function get($key) {
        if($this->redis == false) return false;
        $result = $this->redis->get($key);
        return $result;
    }

    /**
     * 删除一条数据key
     * @param string $key 删除KEY的名称
     */
    public function del($key) {
        if($this->redis == false) return false;
        return Yii::$app->redis->del($key);
    }
    
    /**
     * Adds an item to the start of the list
     * @param mixed $item the item to add
     * @return boolean true if the item was added, otherwise false
     */
    public function unshift($name,$item) {
        if (!$this->redis->lpush($name,$item)) {
            return false;
        }
        return true;
    }
    
      /**
     * Adds an item to the start of the list
     * @param mixed $item the item to add
     * @return boolean true if the item was added, otherwise false
     */
    public function lpush($key,$val) {
        if (!$this->redis->lpush($key,$val)) {
            return false;
        }
        return true;
    }

    /**
     * Removes and returns the first item from the list
     * @return mixed the item that was removed from the list
     */
    public function lpop($key) {
        $item = $this->redis->lpop($key);
        return $item;
    }

    /**
     * 获取缓存队列长度
     */
    public function llen($key){
        $count = (int)$this->redis->llen($key);
        return $count;
    }

    /**
     * 添加无序集合
     * @param string $key 集合的key
     * @param string $value 集合元素的值
     * @param int $timeOut 过期时间
     * @return boolean
     */
    public function sadd($key,$value,$timeOut=0){
        $result = $this->redis->sadd($key,$value);
        if($timeOut){
            $this->redis->expire($key, $timeOut);
        }
        return $result;
    }


    /**
     * 判断redis key值是否存在
     * @param string $key redis key值
     * @return boolean
     */
    public function checkKeyExists($key){
        $result = $this->redis->exists($key);
        return $result;
    }

    /**
     * 查看集合长度
     * @param string $key redis key值
     * @return int
     */
    public function getListSize($key){
        return $this->redis->scard($key);
    }

    /**
     * 设置redis的过期时间 单位秒
     * @param string $key redis的key 值
     * @param int $expire 过期时间 秒
     * @return boolean
     */
    public function setTimeOut($key,$expire=0){
        if($expire>0){
            return $this->redis->expire($key, $expire);
        }
        return false;
    }
}