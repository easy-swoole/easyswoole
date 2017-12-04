<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/2
 * Time: 下午9:41
 */

namespace EasySwoole\Core;


use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Utility\File;
use EasySwoole\Event;

class Core
{
    use Singleton;

    public function initialize():Core
    {
        Di::getInstance()->set(SysConst::VERSION,'2.0.1');
        Event::frameInitialize();
        $this->sysDirectoryInit();
        Event::frameInitialized();
        return $this;
    }

    public function run():void
    {

    }


    private function sysDirectoryInit(){
        //创建临时目录
        $tempDir = Di::getInstance()->get(SysConst::DIR_TEMP);
        if(empty($tempDir)){
            $tempDir = "Temp";
            Di::getInstance()->set(SysConst::DIR_TEMP,$tempDir);
        }
        if(!File::createDir($tempDir)){
            die("create Temp Directory:{$tempDir} fail");
        }
        //创建日志目录
        $logDir = Di::getInstance()->get(SysConst::DIR_LOG);
        if(empty($logDir)){
            $logDir = "Log";
            Di::getInstance()->set(SysConst::DIR_LOG,$logDir);
        }
        if(!File::createDir($logDir)){
            die("create log Directory:{$logDir} fail");
        }
    }
}