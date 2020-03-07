<?php


namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Process\Manager;
use EasySwoole\Component\TableManager;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Crontab\CronRunner;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\Task\TaskManager;

class BaseService extends AbstractProcess
{

    private $processJsonFile;
    private $serverStatusJsonFile;
    private $cronTabJsonFile;
    protected function run($arg)
    {
        /*
         * 本进程用于做Easyswoole后续的一些基础附加服务
         */
        $this->processJsonFile = EASYSWOOLE_TEMP_DIR.'/process.json';
        $this->serverStatusJsonFile = EASYSWOOLE_TEMP_DIR.'/status.json';
        $this->cronTabJsonFile = EASYSWOOLE_TEMP_DIR.'/crontab.json';
        Timer::getInstance()->loop(1*1500,function (){
            //落地进程信息
            $list = Manager::getInstance()->info();
            file_put_contents($this->processJsonFile,json_encode($list,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
            //落地Crontab进程信息
            //落地Task进程信息
            TaskManager::getInstance()->status();
            //落地server status
            $info = ServerManager::getInstance()->getSwooleServer()->stats();
            file_put_contents($this->serverStatusJsonFile,json_encode($info,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
            //落地crontab信息
            $info = Crontab::getInstance()->info();
            file_put_contents($this->cronTabJsonFile,json_encode($info,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
        });
    }

    protected function onShutDown()
    {
        if(is_file($this->processJsonFile)){
            unlink($this->processJsonFile);
        }
        if(is_file($this->serverStatusJsonFile)){
            unlink($this->serverStatusJsonFile);
        }
    }
}