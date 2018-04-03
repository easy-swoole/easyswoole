<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 2018/3/11
 * Time: 22:38
 */

namespace App\Vendor\Db;



//封装swoole_mysql的异步mysql
use EasySwoole\Config;

class AsyncMysql
{
    private static $instance;

//    //尝试连接当前的次数
//    protected $tryConnectTimes = 0;
//
//    //最大尝试次数
//    protected $maxTryConnectTimes = 3;

    /**
     * @var \swoole_mysql
     */
    private $db;

    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new AsyncMysql();
        }
        return self::$instance;
    }

    function __construct(){
        $this->db = new \swoole_mysql;
    }

    /**
     * 异步执行sql
     * @param string $sql
     * @return bool
     * @throws
     */
    function query($sql){
        $conf = Config::getInstance()->getConf("MASTER_MYSQL");
        //格式化mysql配置
        $this->formatConf($conf);
        $this->db->connect($conf, function (\swoole_mysql $db, bool $result) use ($sql){
            if($result === false){
                //执行失败处理 TODO
                return;
            }
            $db->query($sql, function (\swoole_mysql $db, $result){
                $db->close();//需要关闭异步mysql连接
            });
        });
        return true;
    }

    /**
     * 将对于的mysql总配置文件key 转为异步mysql对于的配置key
     * @param $conf
     */
    private function formatConf(&$conf){
        $conf['user'] = $conf['username'];
        $conf['database'] = $conf['db'];
        unset($conf['username']);
        unset($conf['db']);
    }
}