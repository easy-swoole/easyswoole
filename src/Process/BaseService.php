<?php


namespace EasySwoole\EasySwoole\Process;


use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\Component\Timer;

class BaseService extends AbstractProcess
{
    protected function run($arg)
    {
        /*
         * 本进程用于做Easyswoole后续的一些基础附加服务
         */
        $dir = EASYSWOOLE_TEMP_DIR.'/process.json';
        Timer::getInstance()->loop(1*1000,function ()use($dir){
            $list = Manager::getInstance()->info();
            file_put_contents($dir,json_encode($list,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
        });
    }
}