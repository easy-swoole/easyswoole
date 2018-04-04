<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 2018/3/5
 * Time: 22:14
 */

namespace App\Model;


use EasySwoole\Core\Component\Di;

class BaseModel
{
    /**
     * read db
     * @var \MysqliDb
     */
    protected $readDb;
    /**
     * master db
     * @var \MysqliDb
     */
    protected $db;

    /**
     * 异步mysql连接
     * @var \App\Vendor\Db\AsyncMysql
     */
    protected $asyncMysql;

    /**
     * mongodb manager
     * @var \MongoDB\Driver\Manager
     */
//    protected $mongoManager;

    /**
     * mongodb 中的数据埋点库名称
     * @var string
     */
//    protected $mongodbName = "datamine.";

    /**
//     * @var \Elasticsearch\Client
     */
//    protected $elastic;

    function __construct()
    {
//        $this->mongoManager = Di::getInstance()->get('master_mongodb')->getManager();
        $this->db = Di::getInstance()->get("MYSQL_MASTER");
        $this->readDb = Di::getInstance()->get("MYSQL_SLAVE");
        //异步mysql连接
//        $this->asyncMysql = Di::getInstance()->get("ASYNC_MYSQL_MASTER");
//        $this->elastic = Di::getInstance()->get("master_elastic")->getClient();
    }
}