<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/2
 * Time: 下午9:41
 */

namespace easySwoole\Core;


use easySwoole\Core\AbstractInterface\Singleton;
use easySwoole\Core\Component\Di;
use easySwoole\Core\Component\SysConst;
use easySwoole\Core\Utility\File;

class Core
{
    use Singleton;

    public function initialize():Core
    {
        Di::getInstance()->set(SysConst::VERSION,'2.0.1');
        $event = Di::getInstance()->get(SysConst::EVENT_FRAME_INITIALIZE);
        $this->sysDirectoryInit();
        if(is_callable($event)){
            call_user_func($event);
        }
        $event = Di::getInstance()->get(SysConst::EVENT_FRAME_INITIALIZED);
        if(is_callable($event)){
            call_user_func($event);
        }
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