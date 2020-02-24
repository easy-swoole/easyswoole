<?php


namespace EasySwoole\EasySwoole\Process;


use EasySwoole\Component\Process\AbstractProcess;
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
            $list = Manager::getInstance()->info();
            file_put_contents($this->processJsonFile,json_encode($list,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
        });
    }

    protected function onShutDown()
    {
        if(is_file($this->processJsonFile)){
            unlink($this->processJsonFile);
        }
    }
}