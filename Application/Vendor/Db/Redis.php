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

    //尝试连接当前的次数
    protected $tryConnectTimes = 0;

    //最大尝试次数
    protected $maxTryConnectTimes = 3;

    /**
     * @var \Redis
     */
    private $con;

    public static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new Redis();
        }
        return self::$instance;
    }

    function __construct(){
        $this->connect();
    }

    function connect(){
        $conf = Config::getInstance()->getConf("REDIS");
        $this->con = new \Redis();
        $this->con->connect($conf['host'], $conf['port'], $conf['timeout']);
        $this->con->auth($conf['auth']);
        $this->con->select($conf['db']);
        if(!$this->con->ping()){
            if($this->tryConnectTimes <= $this->maxTryConnectTimes){
                return $this->connect();
            }else{
                trigger_error("redis connect fail");
                return null;
            }
        }
        self::$instance = $this;
//        $this->con->setOption(\Redis::OPT_SERIALIZER,\Redis::SERIALIZER_PHP); 暂时不开启php序列化
    }

    /**
     * @return \Redis
     */
    function getConnect(){
        //ping 如果能ping通直接连接，ping不通则尝试重连
        if($this->con->ping()){
            return $this->con;
        }
        $this->tryConnectTimes = 0; //当前重连次数归零, 如果没有归零则会导致redis恢复后不会重连了
        $this->connect();
    }
}