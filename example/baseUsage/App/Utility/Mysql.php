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