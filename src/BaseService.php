<?php


namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Process\Manager;
use EasySwoole\Component\Timer;

class BaseService extends AbstractProcess
{

    private $processJsonFile;
    protected function run($arg)
    {
        /*
         * 本进程用于做Easyswoole后续的一些基础附加服务
         */
        $this->processJsonFile = EASYSWOOLE_TEMP_DIR.'/process.json';
        Timer::getInstance()->loop(1*1000,function (){
            //落地进程信息
            $list = Manager::getInstance()->info();
            file_put_contents($this->processJsonFile,json_encode($list,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
            //落地Crontab进程信息
            //落地Task进程信息
        });
    }

    protected function onShutDown()
    {
        if(is_file($this->processJsonFile)){
            unlink($this->processJsonFile);
        }
    }
}