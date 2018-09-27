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
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventHelper;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Http\WebService;
use EasySwoole\Trace\Bean\Location;
use EasySwoole\EasySwoole\Swoole\PipeMessage\Message;
use EasySwoole\EasySwoole\Swoole\PipeMessage\OnCommand;
use EasySwoole\EasySwoole\Swoole\Task\AbstractAsyncTask;
use EasySwoole\EasySwoole\Swoole\Task\SuperClosure;

class Core
{
    use Singleton;

    private $isDev = true;

    function __construct()
    {
        defined('SWOOLE_VERSION') or define('SWOOLE_VERSION',intval(phpversion('swoole')));
        defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT',realpath(getcwd()));
    }

    function setIsDev(bool $isDev)
    {
        $this->isDev = $isDev;
        return $this;
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
        //加载配置文件
        $this->loadEnv();
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
        $this->registerDefaultCallBack(ServerManager::getInstance()->getSwooleServer(),$conf['SERVER_TYPE']);
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
        //设置目录
        Logger::getInstance($logDir);
    }

    private function registerErrorHandler()
    {
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);
        $userHandler = Di::getInstance()->get(SysConst::ERROR_HANDLER);
        if(!is_callable($userHandler)){
            $userHandler = function($errorCode, $description, $file = null, $line = null){
                $l = new Location();
                $l->setFile($file);
                $l->setLine($line);
                Trigger::getInstance()->error($description,$l);
            };
        }
        set_error_handler($userHandler);

        $func = Di::getInstance()->get(SysConst::SHUTDOWN_FUNCTION);
        if(!is_callable($func)){
            $func = function (){
                $error = error_get_last();
                if(!empty($error)){
                    $l = new Location();
                    $l->setFile($error['file']);
                    $l->setLine($error['line']);
                    Trigger::getInstance()->error($error['message'],$l);
                }
            };
        }
        register_shutdown_function($func);
    }

    private function registerDefaultCallBack(\swoole_server $server,string $serverType)
    {
        //如果主服务仅仅是swoole server，那么设置默认onReceive为全局的onReceive
        if($serverType === ServerManager::TYPE_SERVER){
            $server->on(EventRegister::onReceive,function (\swoole_server $server, int $fd, int $reactor_id, string $data){
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
                $max = 100;
            }
            $webService = new WebService($namespace,$depth,$max);
            $httpExceptionHandler = Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER);
            if($httpExceptionHandler){
                $webService->setExceptionHandler($httpExceptionHandler);
            }
            EventHelper::on($server,EventRegister::onRequest,function (\swoole_http_request $request,\swoole_http_response $response)use($webService){
                $request_psr = new Request($request);
                $response_psr = new Response($response);
                try{
                    if(EasySwooleEvent::onRequest($request_psr,$response_psr)){
                        $webService->onRequest($request_psr,$response_psr);
                    }
                }catch (\Throwable $throwable){
                    Trigger::getInstance()->throwable($throwable);
                }finally{
                    try{
                        EasySwooleEvent::afterRequest($request_psr,$response_psr);
                    }catch (\Throwable $throwable){
                        Trigger::getInstance()->throwable($throwable);
                    }
                }
            });
        }
        //注册默认的on task,finish  不经过 event register。因为on task需要返回值。不建议重写onTask,否则es自带的异步任务事件失效
        EventHelper::on($server,EventRegister::onTask,function (\swoole_server $server, $taskId, $fromWorkerId,$taskObj){
            if(is_string($taskObj) && class_exists($taskObj)){
                $taskObj = new $taskObj;
            }
            if($taskObj instanceof AbstractAsyncTask){
                try{
                    $ret =  $taskObj->run($taskObj->getData(),$taskId,$fromWorkerId);
                    //在有return或者设置了结果的时候  说明需要执行结束回调
                    $ret = is_null($ret) ? $taskObj->getResult() : $ret;
                    if(!is_null($ret)){
                        $taskObj->setResult($ret);
                        return $taskObj;
                    }
                }catch (\Throwable $throwable){
                    $taskObj->onException($throwable);
                }
            }else if($taskObj instanceof SuperClosure){
                try{
                    return $taskObj( $server, $taskId, $fromWorkerId);
                }catch (\Throwable $throwable){
                    Trigger::getInstance()->throwable($throwable);
                }
            }
            return null;
        });
        EventHelper::on($server,EventRegister::onFinish,function (\swoole_server $server, $taskId, $taskObj){
            //finish 在仅仅对AbstractAsyncTask做处理，其余处理无意义。
            if($taskObj instanceof AbstractAsyncTask){
                try{
                    $taskObj->finish($taskObj->getResult(),$taskId);
                }catch (\Throwable $throwable){
                    $taskObj->onException($throwable);
                }
            }
        });

        //注册默认的pipe通讯
        OnCommand::getInstance()->set('TASK',function ($fromId,$taskObj){
            if(is_string($taskObj) && class_exists($taskObj)){
                $taskObj = new $taskObj;
            }
            if($taskObj instanceof AbstractAsyncTask){
                try{
                    $taskObj->run($taskObj->getData(),ServerManager::getInstance()->getSwooleServer()->worker_id,$fromId);
                }catch (\Throwable $throwable){
                    $taskObj->onException($throwable);
                }
            }else if($taskObj instanceof SuperClosure){
                try{
                    $taskObj();
                }catch (\Throwable $throwable){
                    Trigger::getInstance()->throwable($throwable);
                }
            }
        });

        EventHelper::on($server,EventRegister::onPipeMessage,function (\swoole_server $server,$fromWorkerId,$data){
            $message = \swoole_serialize::unpack($data);
            if($message instanceof Message){
                OnCommand::getInstance()->hook($message->getCommand(),$fromWorkerId,$message->getData());
            }else{
                Trigger::getInstance()->error("data :{$data} not packet by swoole_serialize or not a Message Instance");
            }
        });
    }

    private function loadEnv()
    {
        if($this->isDev){
            $file  = EASYSWOOLE_ROOT.'/dev.env';
        }else{
            $file  = EASYSWOOLE_ROOT.'/produce.env';
        }
        Config::getInstance()->loadEnv($file);
    }
}