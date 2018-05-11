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
    private $poolSize = 20;

    /**
     * 里面包含CoMysql
     * @var array
     */
    private $pool = array();

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


    /**
     * 获取连接池连接
     * @return bool|Mysql
     */
    function getConnect() {
        //首先查看连接池大小是否超过最大尺寸,
        if($mysql = array_pop($this->pool)){
            /**
             * @var $mysql \Swoole\Coroutine\Mysql
             */
            return $mysql;
        }
        else{
            return false;
        }
    }

    /**
     * 释放连接
     * @param $mysql
     * @return bool|int
     */
    function freeConnect($mysql)
    {
        //如果实际连接池大小小于等于连接池最大值,则直接将此连接压入堆栈， 否则直接释放
        if(count($this->pool) <= $this->poolSize){
            return array_push($this->pool, $mysql);
        }
        return true;
    }


}