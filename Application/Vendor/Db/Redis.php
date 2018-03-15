<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 2018/3/11
 * Time: 22:38
 */

namespace App\Vendor\Db;


use EasySwoole\Config;

class Redis
{
    private static $instance;

    /**
     * @var \Redis
     */
    private $con;

    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new \Redis();
        }
        return self::$instance;
    }

    function __construct()
    {
        $conf = Config::getInstance()->getConf("REDIS");
        $this->con = new \Redis();
        $this->con->connect($conf['host'], $conf['port'], 3);
        $this->con->auth($conf['auth']);
        $this->con->select($conf['db']);
//        $this->con->setOption(\Redis::OPT_SERIALIZER,\Redis::SERIALIZER_PHP); 暂时不开启php序列化
    }

    /**
     * @return \Redis
     */
    function getConnect(){
        return $this->con;
    }
}