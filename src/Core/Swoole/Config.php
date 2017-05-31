<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2016/12/11
 * Time: 02:44
 */

namespace Core\Swoole;
use Conf\Config as GlobalConf;

class Config
{
    protected static $instance;
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }
    function listenIp(){
        return GlobalConf::getInstance()->getConf("SERVER.LISTEN");
    }
    function listenPort(){
        return GlobalConf::getInstance()->getConf("SERVER.PORT");
    }
    function workerSetting(){
        return GlobalConf::getInstance()->getConf("SERVER.CONFIG");
    }
    function allWorkerNum(){
        return GlobalConf::getInstance()->getConf("SERVER.CONFIG.worker_num");
    }
    function allTaskWorkerNum(){
        return GlobalConf::getInstance()->getConf("SERVER.CONFIG.task_worker_num");
    }
    function serverName(){
        return GlobalConf::getInstance()->getConf("SERVER.SERVER_NAME");
    }
    function wsSupport(){
        return GlobalConf::getInstance()->getConf("SERVER.WS_SUPPORT");
    }
}