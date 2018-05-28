<?php
/**
 * Created by PhpStorm.
 * User: 111
 * Date: 2018/5/11
 * Time: 16:14
 */

namespace App\Vendor\Db;
use EasySwoole\Config;
use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Logger;
use Swoole\Coroutine\Mysql;

/**
 * Class CoMysqlPool swoole mysql协程连接池
 * @package App\Vendor\Db
 */
class CoMysqlPool
{
    /**
     * @var int 连接池大小
     */
    private $poolSize = 50;

    /**
     * 里面包含CoMysql
     * @var array
     */
    private $pool = array();

    //重连尝试次数
    private $tryTimes = 0;

    //最大重连次数
    private $tryMaxTimes = 3;

    use Singleton;

    public function __construct(){
        //初始化连接池
        for ($i=0; $i < $this->poolSize; $i++) {
            $mysql = new Mysql();
            $Conf = Config::getInstance()->getConf("CO_MYSQL");
            $ret = $mysql->connect($Conf);
            if($ret){
                array_push($this->pool, $mysql);
            }
            else{
                throw new \Exception("mysqlpool init fail");
            }
        }
    }

    //连接mysql
    private function connect() :?Mysql{
        //超过最大重连尝试次数, 则退出
        if($this->tryTimes > $this->tryMaxTimes){
            return null;
        }
        //如果尝试次数还没达最大值,  继续尝试
        $mysql = new Mysql();
        $Conf = Config::getInstance()->getConf("CO_MYSQL");
        $ret = $mysql->connect($Conf);
        if($ret){
            return $mysql;
        }
        $this->tryTimes++;
        unset($mysql);
        return $this->connect();
    }


    /**
     * 获取连接池连接, null表示没有空闲连接
     * @return null|Mysql
     */
    function getConnect() :?Mysql {
//        Logger::getInstance()->log("协程池空闲数:" . count($this->pool) . "; 协程池总大小:" . $this->poolSize);
        /**
         * @var $mysql \Swoole\Coroutine\Mysql
         */
        $mysql = $mysql = array_pop($this->pool);
        //判断连接是否有效, 如果失效重新连接
        if(!$mysql->connected){
            Logger::getInstance()->log("mysqlCo: 断开连接");
            $this->tryTimes = 0; //每次重连前先清空尝试次数
            $newMysql = $this->connect();
            if(!empty($newMysql)){
                unset($mysql);
                Logger::getInstance()->log("mysqlCo: 重连成功");
                return $newMysql;
            }
            else{
                //重连失败, 将原$mysql放回协程池
                array_push($this->pool, $mysql);
                Logger::getInstance()->log("mysqlCo: 重连失败");
                return null;
            }
        }
        return $mysql;
    }

    /**
     * 释放连接
     * @param $mysql
     * @return bool|int
     */
    function freeConnect(Mysql $mysql)
    {
        //如果实际连接池大小小于等于连接池最大值,则直接将此连接压入堆栈， 否则直接释放
        if(count($this->pool) <= $this->poolSize){
            return array_push($this->pool, $mysql);
        }
        else{
            unset($mysql);
        }
        return true;
    }


}