<?php

/**
 * Created by PhpStorm.
 * User: YF
 * Date: 16/8/25
 * Time: 上午12:05
 */
namespace Conf;

class Config
{
    private static $instance;
    protected $conf;
    function __construct()
    {
        $this->init();
    }
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }
    private function init(){
        $sysConf = array(
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
//                    "dispatch_model"=>1,//3为抢占模式 不对繁忙进程发送任务
//						'task_ipc_mode'=>2,
                    "open_cpu_affinity"=>1,
                    "daemonize"=>false,
//                    "user"=>"yf",
//                    "group"=>"root",
                    "log_file"=>ROOT.'/swoole_log.txt'
                ),
            ),
            "DEBUG"=>array(
                "LOG"=>0,
                "DISPLAY_ERROR"=>0,
                "ENABLE"=>false,
            ),
        );
        $userConf = array();
        $this->conf = $sysConf+$userConf;
    }
    function getConf($key){
        if(isset($this->conf[$key])){
            return $this->conf[$key];
        }else{
            return null;
        }
    }
    /*
         * 在server启动以后，无法动态的去添加，修改配置信息（进程数据独立）
    */
    function setConf($key,$data){
        $this->conf[$key] = $data;
    }
}