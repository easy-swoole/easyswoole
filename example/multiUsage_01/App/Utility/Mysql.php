<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/22
 * Time: 上午12:14
 */

namespace App\Utility;


use Conf\Config;

class Mysql
{
    protected $db;
    protected static $instance;
    function __construct()
    {
        //未处理断线重连问题    简单粗暴的，就是在单利模式获取连接的时候   判断连接创建时间
        //然后比如，一个连接的有效时间设置为30s，超过的时候 就重连
        $conf = Config::getInstance()->getConf("MYSQL");
        $this->db =  new \MysqliDb(
            $conf['HOST'],
            $conf['USER'],
            $conf['PASSWORD'],
            $conf['DB_NAME']
        );
    }

    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new Mysql();
        }
        return self::$instance;
    }

    function getDb(){
        return $this->db;
    }

    public static function getLimit($page = 1,$page_num = 10){
        if($page >= 1){
            $limit = Array(($page-1)*$page_num,$page_num);
        }else{
            $limit = Array(0,$page_num);
        }
        return $limit;
    }
}