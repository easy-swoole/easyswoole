<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 2018/3/5
 * Time: 22:14
 */

namespace App\Model;


use EasySwoole\Core\Component\Di;

class BaseModel extends Model
{
    function __construct(){
//        $this->mongoManager = Di::getInstance()->get('master_mongodb')->getManager();
        $this->db = Di::getInstance()->get("MYSQL_MASTER");
        $this->readDb = Di::getInstance()->get("MYSQL_SLAVE");
        //异步mysql连接
//        $this->asyncMysql = Di::getInstance()->get("ASYNC_MYSQL_MASTER");
//        $this->elastic = Di::getInstance()->get("master_elastic")->getClient();
    }
}