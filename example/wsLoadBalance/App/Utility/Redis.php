<?php
/**
 * Created by PhpStorm.
 * User: liu
 * Date: 2017/7/18
 * Time: 17:39
 * redis操作帮助类
 */

namespace App\Utility;


use Conf\Config;
use Core\Component\Logger;

class Redis {
    private static $instance;
    private $config;
    private $_redis;
    function __construct() {
        $this->config = Config::getInstance()->getConf("REDIS");
        $this->connect();
    }

    static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            if ($this->_redis) {
                unset($this->_redis);
            }
            $this->_redis = new \Redis();
            if ($this->config['pconnect']) {
                $this->_redis->pconnect($this->config['host'], $this->config['port'], $this->config['timeout']);
            } else {
                $this->_redis->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
            }

            if (!empty($this->config['password'])) {
                $this->_redis->auth($this->config['password']);
            }
            if (!empty($this->config['database'])) {
                $this->_redis->select($this->config['database']);
            }
        } catch (\RedisException $e) {
            trigger_error('redis exception');
            return false;
        }
    }
    function __call($method, $args = array()) {
        $connectCount = 1;
        while (true) {
            try {
                $result = call_user_func_array(array($this->_redis, $method), $args);
            } catch (\RedisException $e) {
                //捕获链接失败异常
                if($connectCount >= $this->config['max_recount']){
                    //超过重连次数
                    throw new \Exception("redis reconnect fail");
                }
                Logger::log('redis connect fail');
                if ($this->_redis->isConnected()) {
                    $this->_redis->close();
                }
                $this->connect();
                $connectCount ++ ;
                continue;
            }
            return $result;
        }
    }
}