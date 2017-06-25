<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/25
 * Time: 下午4:47
 */

namespace App\Model;


use App\Utility\Mysql;

class Test
{
    private $db;
    function __construct()
    {
        $this->db = Mysql::getInstance();
    }

    function modelTest(){
        /*
         * 请务必使得数据库配置且存在表 test，关于数据库对象的用法，
         * 更对请见https://github.com/joshcam/PHP-MySQLi-Database-Class
         *
         */

        return $this->db->getDb()->get("test");
    }
}