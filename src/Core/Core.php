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
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\EasySwooleEvent;

class Core
{
    use Singleton;

    public function __construct()
    {
        defined('SWOOLE_VERSION') or define('SWOOLE_VERSION',intval(phpversion('swoole')));
        defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT',realpath(getcwd()));
        if(file_exists(EASYSWOOLE_ROOT.'/EasySwooleEvent.php')){
            require_once EASYSWOOLE_ROOT.'/EasySwooleEvent.php';
        }
        $this->sysDirectoryInit();
    }

    public function initialize():Core
    {
        Di::getInstance()->set(SysConst::VERSION,'2.1.2');
        Di::getInstance()->set(SysConst::HTTP_CONTROLLER_MAX_DEPTH,3);
        EasySwooleEvent::frameInitialize();
        $this->errorHandle();
        return $this;
    }

    public function run():void
    {
        ServerManager::getInstance()->start();
    }

    private function sysDirectoryInit():void
    {
        //创建临时目录    请以绝对路径，不然守护模式运行会有问题
        $tempDir = Config::getInstance()->getConf('TEMP_DIR');
        if(empty($tempDir)){
            Config::getInstance()->setConf('TEMP_DIR',EASYSWOOLE_ROOT.'/Temp');
            $tempDir = EASYSWOOLE_ROOT.'/Temp';
        }

        $logDir = Config::getInstance()->getConf('LOG_DIR');
        if(empty($logDir)){
            Config::getInstance()->setConf('LOG_DIR',EASYSWOOLE_ROOT.'/Log');
            $logDir = EASYSWOOLE_ROOT.'/Temp';
        }

        Config::getInstance()->setConf('MAIN_SERVER.SETTING.pid_file',$tempDir.'/pid.pid');
        Config::getInstance()->setConf('MAIN_SERVER.SETTING.log_file',$logDir.'/swoole.log');
    }

    private function errorHandle():void
    {
        $conf = Config::getInstance()->getConf("DEBUG");
        if(!$conf){
            return;
        }
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);
        $userHandler = Di::getInstance()->get(SysConst::ERROR_HANDLER);
        if(!is_callable($userHandler)){
            $userHandler = function($errorCode, $description, $file = null, $line = null){
                Trigger::error($description,$file,$line,$errorCode);
            };
        }
        set_error_handler($userHandler);

        $func = Di::getInstance()->get(SysConst::SHUTDOWN_FUNCTION);
        if(!is_callable($func)){
            $func = function ()use($conf){
                $error = error_get_last();
                if(!empty($error)){
                    Trigger::error($error['message'],$error['file'],$error['line']);
                }
            };
        }
        register_shutdown_function($func);
    }
}