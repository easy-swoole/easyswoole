<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2016/12/11
 * Time: 02:44
 */

namespace Core\Swoole;


class Config
{
    protected static $instance;
    protected $conf;
    function __construct()
    {
        $this->conf = \Conf\Config::getInstance()->getConf("SERVER");
    }
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }
    function listenIp(){
        return $this->conf['LISTEN'];
    }
    function listenPort(){
        return $this->conf['PORT'];
    }
    function workerSetting(){
        return $this->conf['CONFIG'];
    }
    function allWorkerNum(){
        return $this->conf['CONFIG']['worker_num'];
    }
    function allTaskWorkerNum(){
        return $this->conf['CONFIG']['task_worker_num'];
    }
    function nodeName(){
        return $this->conf['NODE_NAME'];
    }
    function serverName(){
        return $this->conf['SERVER_NAME'];
    }
}