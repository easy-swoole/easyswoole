<?php
/**
 * Created by PhpStorm.
 * User: 111
 * Date: 2018/4/3
 * Time: 16:59
 */

namespace App\Vendor\Db;


//封装异步redis
use EasySwoole\Config;

class AsyncRedis
{
    private static $instance;

    /**
     * @var \swoole_redis
     */
    private $redis;

    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new AsyncRedis();
        }
        return self::$instance;
    }

    function __construct(){
        $conf = Config::getInstance()->getConf("REDIS");
        $conf = $this->formatConf($conf);
        $this->redis = new \swoole_redis($conf);
    }

    /**
     * 异步执行redis set操作
     * @param string $key 键
     * @param string $value 值
     * @return bool
     * @throws
     */
    function set(string $key, string $value) :bool {
        $conf = Config::getInstance()->getConf("REDIS");
        $this->redis->connect($conf['host'], $conf['port'] , function (\swoole_redis $redis, bool $result) use ($key, $value){
            if($result === false){
                //执行失败处理 TODO
                return;
            }
            $redis->set($key, $value, function(\swoole_redis $redis, $result){
//                $redis->close();//需要关闭异步redis连接
            });
        });
        return true;
    }


    /**
     *初始化redis配置
     * @param $conf
     * @return array
     */
    private function formatConf($conf){
        $newConf = array();
        if(is_int($conf["db"])){
            $newConf['database'] = $conf['db'];
        }
        if(!empty($conf['auth'])){
            $newConf['password'] = $conf['auth'];
        }
        $newConf['timeout'] = $conf['timeout'];
        return $newConf;
    }
}