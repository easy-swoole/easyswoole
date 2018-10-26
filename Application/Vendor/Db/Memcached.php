<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 2018/3/11
 * Time: 22:38
 */

namespace App\Vendor\Db;


use EasySwoole\Config;

class Memcached
{
    private static $instance;

    //尝试连接当前的次数
    protected $tryConnectTimes = 0;

    //最大尝试次数
    protected $maxTryConnectTimes = 3;

    /**
     * @var \Memcached
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
        $conf = Config::getInstance()->getConf("testconf.MEMCACHED");
        $this->con = new \Memcached();
        //添加memcache池
        $this->con->addServers(array(
            array($conf['host'], $conf['port'], 100)
        ));
        //如果获取memcache状态失败则尝试重连
        if(!$this->con->getVersion()){
            if($this->tryConnectTimes <= $this->maxTryConnectTimes){
                $this->tryConnectTimes++;
                return $this->connect();
            }else{
                trigger_error("memcached connect fail");
                return null;
            }
        }
        self::$instance = $this;
    }

    /**
     * 获取memcached连接
     * @return \Memcached
     */
    function getConnect(){
        //ping 如果能ping通直接连接，ping不通则尝试重连
        if($this->con->getVersion()){
            return $this->con;
        }
        $this->tryConnectTimes = 0; //当前重连次数归零, 如果没有归零则会导致redis恢复后不会重连了
        $this->connect();
    }
}