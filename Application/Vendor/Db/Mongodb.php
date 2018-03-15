<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 2017/11/8
 * Time: 23:25
 */
namespace App\Vendor\Db;

use EasySwoole\Config;

class Mongodb
{
    private static $instance;

    private $manager;

    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new Mongodb();
        }
        return self::$instance;
    }


    function __construct()
    {
        $this->connect();
    }

    function connect(){
        $conf = Config::getInstance()->getConf("MONGODB");
        $specialConf = [];
        if(isset($conf['password']) && !empty($conf['password'])){
            $specialConf['password'] = $conf['password'];
        }
        if(isset($conf['username']) && !empty($conf['username'])){
            $specialConf['username'] = $conf['username'];
        }
        if(isset($conf['authSource']) && !empty($conf['authSource'])){
            $specialConf['authSource'] = $conf['authSource'];
        }
        if(empty($specialConf)){
            $this->manager = new \MongoDB\Driver\Manager("mongodb://".$conf["host"].":".$conf["port"]);
        }
        else{
            $this->manager = new \MongoDB\Driver\Manager("mongodb://".$conf["host"].":".$conf["port"], $specialConf);
        }
    }

    /**
     * @return mixed|\MongoDB\Driver\Manager
     */
    function getManager(){
        return $this->manager;
    }

    /**
     * @return \MongoDB\Driver\BulkWrite
     */
    function getBulk(){
        return new \MongoDB\Driver\BulkWrite;
    }
}