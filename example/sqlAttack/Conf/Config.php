<?php

/**
 * Created by PhpStorm.
 * User: YF
 * Date: 16/8/25
 * Time: 上午12:05
 */
namespace Conf;

use Core\Component\Spl\SplArray;

class Config
{
    private static $instance;
    protected $conf;
    function __construct()
    {
        $conf = $this->sysConf()+$this->userConf();
        $this->conf = new SplArray($conf);
    }
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }
    function getConf($keyPath){
        return $this->conf->get($keyPath);
    }
    /*
            * 在server启动以后，无法动态的去添加，修改配置信息（进程数据独立）
    */
    function setConf($keyPath,$data){
        $this->conf->set($keyPath,$data);
    }

    private function sysConf(){
        return array(
            "SERVER"=>array(
                "LISTEN"=>"0.0.0.0",
                "SERVER_NAME"=>"",
                "PORT"=>9501,
                "WS_SUPPORT"=>false,
                "CONFIG"=>array(
                    'task_worker_num' => 4, //异步任务进程
                    "task_max_request"=>10,
                    'max_request'=>3000,
                    'worker_num'=>4,
                    "log_file"=>ROOT.'/Log/swoole_log.txt',
                    'pid_file'=>ROOT."/Log/pid.pid",
                ),
            ),
            "DEBUG"=>array(
                "LOG"=>1,
                "DISPLAY_ERROR"=>1,
                "ENABLE"=>false,
            ),
            "CONTROLLER_POOL"=>true
        );
    }

    private function userConf(){
        return array();
    }
}