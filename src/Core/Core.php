<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/2
 * Time: 下午9:41
 */

namespace EasySwoole\Core;


use EasySwoole\Config;
use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\Logger;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Swoole\Server;
use EasySwoole\Core\Utility\File;
use EasySwoole\Event;

class Core
{
    use Singleton;

    public function initialize():Core
    {
        Di::getInstance()->set(SysConst::VERSION,'2.0.1');
        Di::getInstance()->set(SysConst::APP_NAMESPACE,'App');
        Di::getInstance()->set(SysConst::CONTROLLER_MAX_DEPTH,3);
        Event::frameInitialize();
        $this->sysDirectoryInit();
        $this->errorHandle();
        Event::frameInitialized();
        return $this;
    }

    public function run():void
    {
        $this->initialize();
        Server::getInstance()->start();
    }


    private function sysDirectoryInit():void
    {
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
            $logDir = "Logs";
            Di::getInstance()->set(SysConst::DIR_LOG,$logDir);
        }
        if(!File::createDir($logDir)){
            die("create log Directory:{$logDir} fail");
        }
    }

    private function errorHandle():void
    {
        $conf = Config::getInstance()->getConf("DEBUG");
        if($conf['ENABLE'] != true){
            return;
        }
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);
        $userHandler = Di::getInstance()->get(SysConst::ERROR_HANDLER);
        if(!is_callable($userHandler)){
            $userHandler = function($errorCode, $description, $file = null, $line = null, $context = null)use($conf){
                $str = "{$description} in file {$file} at line {$line}";
                Logger::getInstance()->console($str);
                if(Server::getInstance()->getCurrentFd()){
                    if($conf['RESPONSE']){
                        Server::getInstance()->getServer()->send(Server::getInstance()->getCurrentFd(),$str);
                    }
                    if($conf['AUTO_CLOSE']){
                        Server::getInstance()->getServer()->close(Server::getInstance()->getCurrentFd());
                    }
                }
            };
        }
        set_error_handler($userHandler);

        $func = Di::getInstance()->get(SysConst::SHUTDOWN_FUNCTION);
        if(!is_callable($func)){
            $func = function ()use($conf){
                $error = error_get_last();
                if(!empty($error)){
                    $str = $error['message'].' at file '.$error['file'].' line '.$error['line'];
                    Logger::getInstance()->console($str);
                    if(Server::getInstance()->getCurrentFd()){
                        if($conf['RESPONSE']){
                            Server::getInstance()->getServer()->send(Server::getInstance()->getCurrentFd(),$str);
                        }
                        if($conf['AUTO_CLOSE']){
                            Server::getInstance()->getServer()->close(Server::getInstance()->getCurrentFd());
                        }
                    }
                }
            };
        }
        register_shutdown_function($func);
    }
}