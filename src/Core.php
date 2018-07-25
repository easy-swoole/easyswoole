<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:07
 */

namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Di;
use EasySwoole\Component\Singleton;
use EasySwoole\Core\EventHelper;
use EasySwoole\Core\EventRegister;
use EasySwoole\Core\ServerManager;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Http\WebService;
use EasySwoole\Trigger\Logger;
use EasySwoole\Trigger\Trigger;

class Core
{
    use Singleton;

    function __construct()
    {
        defined('SWOOLE_VERSION') or define('SWOOLE_VERSION',intval(phpversion('swoole')));
        defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT',realpath(getcwd()));
    }

    function initialize()
    {
        //检查全局文件是否存在.
        $file = EASYSWOOLE_ROOT . '/EasySwooleEvent.php';
        if(file_exists($file)){
            require_once $file;
            try{
                $ref = new \ReflectionClass('EasySwoole\EasySwoole\EasySwooleEvent');
                if(!$ref->implementsInterface(Event::class)){
                    die('global file for EasySwooleEvent is not compatible for EasySwoole\EasySwoole\EasySwooleEvent');
                }
                unset($ref);
            }catch (\Throwable $throwable){
                die($throwable->getMessage());
            }
        }else{
            die('global event file missing');
        }
        //执行框架初始化事件
        EasySwooleEvent::initialize();
        //临时文件和Log目录初始化
        $this->sysDirectoryInit();
        //注册错误回调
        $this->registerErrorHandler();
        return $this;
    }

    function createServer()
    {
        $conf = Config::getInstance()->getConf('MAIN_SERVER');
        ServerManager::getInstance()->createSwooleServer(
            $conf['PORT'],$conf['SERVER_TYPE'],$conf['HOST'],$conf['SETTING'],$conf['RUN_MODEL'],$conf['SOCK_TYPE']
        );
        $this->mainServerHook($conf['SERVER_TYPE']);
        EasySwooleEvent::mainServerCreate(ServerManager::getInstance()->getMainEventRegister());
        return $this;
    }

    function start()
    {
        ServerManager::getInstance()->start();
    }

    private function sysDirectoryInit():void
    {
        //创建临时目录    请以绝对路径，不然守护模式运行会有问题
        $tempDir = Config::getInstance()->getConf('TEMP_DIR');
        if(empty($tempDir)){
            $tempDir = EASYSWOOLE_ROOT.'/Temp';
            Config::getInstance()->setConf('TEMP_DIR',$tempDir);
        }
        if(!is_dir($tempDir)){
            mkdir($tempDir);
        }

        $logDir = Config::getInstance()->getConf('LOG_DIR');
        if(empty($logDir)){
            $logDir = EASYSWOOLE_ROOT.'/Log';
            Config::getInstance()->setConf('LOG_DIR',$logDir);
        }
        if(!is_dir($logDir)){
            mkdir($logDir);
        }
        //设置默认文件目录值
        Config::getInstance()->setConf('MAIN_SERVER.SETTING.pid_file',$tempDir.'/pid.pid');
        Config::getInstance()->setConf('MAIN_SERVER.SETTING.log_file',$logDir.'/swoole.log');
        Logger::getInstance()->setLogDir($logDir);
    }

    private function registerErrorHandler()
    {
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
            $func = function (){
                $error = error_get_last();
                if(!empty($error)){
                    Trigger::error($error['message'],$error['file'],$error['line']);
                }
            };
        }
        register_shutdown_function($func);
    }

    private function mainServerHook($type)
    {
        if($type === ServerManager::TYPE_SERVER){
            ServerManager::getInstance()->getSwooleServer()->on(EventRegister::onReceive,function (\swoole_server $server, int $fd, int $reactor_id, string $data){
                EasySwooleEvent::onReceive($server,$fd,$reactor_id,$data);
            });
        }else{
            $namespace = Di::getInstance()->get(SysConst::HTTP_CONTROLLER_NAMESPACE);
            if(empty($namespace)){
                $namespace = 'App\\HttpController\\';
            }
            $depth = intval(Di::getInstance()->get(SysConst::HTTP_CONTROLLER_MAX_DEPTH));
            $depth = $depth > 5 ? $depth : 5;
            $max = intval(Di::getInstance()->get(SysConst::HTTP_CONTROLLER_POOL_MAX_NUM));
            if($max == 0){
                $max = 20;
            }
            $webService = new WebService($namespace,$depth,$max);
            $httpExceptionHandler = Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER);
            if($httpExceptionHandler){
                $webService->setExceptionHandler($httpExceptionHandler);
            }
            $server = ServerManager::getInstance()->getSwooleServer();
            EventHelper::on($server,EventRegister::onRequest,function (\swoole_http_request $request,\swoole_http_response $response)use($webService){
                $request_psr = new Request($request);
                $response_psr = new Response($response);
                if(EasySwooleEvent::onRequest($request_psr,$response_psr)){
                    $webService->onRequest($request_psr,$response_psr);
                }
                EasySwooleEvent::afterRequest($request_psr,$response_psr);
            });
        }
    }
}