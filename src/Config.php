<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/3
 * Time: 下午5:48
 */

namespace EasySwoole;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\Spl\SplArray;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Swoole\Config as swooleConf;


class Config
{
    private $conf;

    use Singleton;

    final public function __construct()
    {
        $conf = $this->sysConf()+$this->userConf();
        $this->conf = new SplArray($conf);
    }

    public function getConf($keyPath)
    {
        return $this->conf->get($keyPath);
    }

    /*
      * 在server启动以后，无法动态的去添加，修改配置信息（进程数据独立）
    */
    public function setConf($keyPath,$data):void
    {
        $this->conf->set($keyPath,$data);
    }

    private function sysConf():array
    {
        return array(
            "SERVER"=>array(
                "LISTEN"=>"0.0.0.0",
                "PORT"=>9501,
                "SERVER_TYPE"=>swooleConf::TYPE_WEB,//
                'SOCKET_TYPE'=>SWOOLE_TCP,//当SERVER_TYPE为SERVER_TYPE_SERVER模式时有效
                "CONFIG"=>array(
                    'task_worker_num' => 8, //异步任务进程
                    "task_max_request"=>10,
                    'max_request'=>5000,//强烈建议设置此配置项
                    'worker_num'=>8,
                    'log_file'=>Di::getInstance()->get(SysConst::DIR_LOG).'/swoole.log',
                    'pid_file'=>Di::getInstance()->get(SysConst::DIR_TEMP).'/pid.pid'
                ),
            ),
            "DEBUG"=>array(
                "RESPONSE"=>true,
                'AUTO_CLOSE'=>true,
                "ENABLE"=>true,
            ),
        );
    }

    private function userConf():array
    {
        return array();
    }
}