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
use EasySwoole\Core\Component\Event;
use EasySwoole\Core\Component\Logger;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\EasySwooleEvent;

class Core
{
    use Singleton;

    public function __construct()
    {
        defined('EASYSWOOLE_ROOT') or define("EASYSWOOLE_ROOT",realpath(getcwd()));
    }

    public function initialize():Core
    {
        Di::getInstance()->set(SysConst::VERSION,'2.0.1');
        Di::getInstance()->set(SysConst::CONTROLLER_MAX_DEPTH,3);
        //创建全局事件容器
        $event = $this->eventHook();
        $this->sysDirectoryInit();
        $event->hook('frameInitialize');
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
        $logDir = Config::getInstance()->getConf('LOG_DIR');
        Di::getInstance()->set(SysConst::DIR_TEMP,$tempDir);
        Di::getInstance()->set(SysConst::DIR_LOG,$logDir);
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
                $debug = debug_backtrace();
                $func = isset($debug[1]['function']) ? $debug[1]['function']: 'unKnown';
                $debug = "file[{$file}] function[{$func}] line[$line]";
                $str = "{$debug} message: [{$description}]";
                Logger::getInstance()->console($str);
            };
        }
        set_error_handler($userHandler);

        $func = Di::getInstance()->get(SysConst::SHUTDOWN_FUNCTION);
        if(!is_callable($func)){
            $func = function ()use($conf){
                $error = error_get_last();
                if(!empty($error)){
                    $debug = debug_backtrace();
                    $func = isset($debug[1]['function']) ? $debug[1]['function']: 'unKnown';
                    $debug = "file[{$error['file']}] function[{$func}] line[{$error['line']}]";
                    $str = "{$debug} message: [{$error['message']}]";
                    Logger::getInstance()->console($str);
                }
            };
        }
        register_shutdown_function($func);
    }

    private function eventHook():Event
    {
        $event = Event::getInstance();
        require EASYSWOOLE_ROOT.'/EasySwooleEvent.php';
        $sysEvent = new EasySwooleEvent();
        $event->add('frameInitialize',[$sysEvent,'frameInitialize']);
        $event->add('mainServerCreate',[$sysEvent,'mainServerCreate']);
        $event->add('onRequest',[$sysEvent,'onRequest']);
        $event->add('afterAction',[$sysEvent,'afterAction']);
        return $event;
    }
}