<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:07
 */

namespace EasySwoole\EasySwoole;


use EasySwoole\Actor\Actor;
use EasySwoole\Component\Di;
use EasySwoole\Component\Singleton;
use EasySwoole\Console\Console;
use EasySwoole\Console\ConsoleModuleContainer;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Console\Module\Auth;
use EasySwoole\EasySwoole\Console\Module\Log;
use EasySwoole\EasySwoole\Console\Module\Server;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\Swoole\EventHelper;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\Swoole\Task\QuickTaskInterface;
use EasySwoole\FastCache\Cache;
use EasySwoole\Http\Dispatcher;
use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Trace\AbstractInterface\LoggerInterface;
use EasySwoole\Trace\AbstractInterface\TriggerInterface;
use EasySwoole\Trace\Bean\Location;
use EasySwoole\EasySwoole\Swoole\Task\AbstractAsyncTask;
use EasySwoole\EasySwoole\Swoole\Task\SuperClosure;
use Swoole\Server\Task;
use EasySwoole\Console\Config as ConsoleConfig;

////////////////////////////////////////////////////////////////////
//                          _ooOoo_                               //
//                         o8888888o                              //
//                         88" . "88                              //
//                         (| ^_^ |)                              //
//                         O\  =  /O                              //
//                      ____/`---'\____                           //
//                    .'  \\|     |//  `.                         //
//                   /  \\|||  :  |||//  \                        //
//                  /  _||||| -:- |||||-  \                       //
//                  |   | \\\  -  /// |   |                       //
//                  | \_|  ''\---/''  |   |                       //
//                  \  .-\__  `-`  ___/-. /                       //
//                ___`. .'  /--.--\  `. . ___                     //
//            \  \ `-.   \_ __\ /__ _/   .-` /  /                 //
//      ========`-.____`-.___\_____/___.-`____.-'========         //
//                           `=---='                              //
//      ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^        //
//         佛祖保佑       永无BUG       永不修改                     //
////////////////////////////////////////////////////////////////////


class Core
{
    use Singleton;

    private $isDev = true;

    function __construct()
    {
        defined('SWOOLE_VERSION') or define('SWOOLE_VERSION',intval(phpversion('swoole')));
        defined('EASYSWOOLE_ROOT') or define('EASYSWOOLE_ROOT',realpath(getcwd()));
        defined('EASYSWOOLE_SERVER') or define('EASYSWOOLE_SERVER',1);
        defined('EASYSWOOLE_WEB_SERVER') or define('EASYSWOOLE_WEB_SERVER',2);
        defined('EASYSWOOLE_WEB_SOCKET_SERVER') or define('EASYSWOOLE_WEB_SOCKET_SERVER',3);
    }

    function setIsDev(bool $isDev)
    {
        $this->isDev = $isDev;
        return $this;
    }

    function isDev():bool
    {
        return $this->isDev;
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
        //先加载配置文件
        $this->loadEnv();
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
            $conf['PORT'],$conf['SERVER_TYPE'],$conf['LISTEN_ADDRESS'],$conf['SETTING'],$conf['RUN_MODEL'],$conf['SOCK_TYPE']
        );
        $this->registerDefaultCallBack(ServerManager::getInstance()->getSwooleServer(),$conf['SERVER_TYPE']);
        //hook 全局的mainServerCreate事件
        EasySwooleEvent::mainServerCreate(ServerManager::getInstance()->getMainEventRegister());
        $this->extraHandler();
        return $this;
    }

    function start()
    {
        //给主进程也命名
        $serverName = Config::getInstance()->getConf('SERVER_NAME');
        if(PHP_OS != 'Darwin'){
            cli_set_process_title($serverName);
        }
        //启动
        ServerManager::getInstance()->start();
    }

    private function sysDirectoryInit():void
    {
        //创建临时目录    请以绝对路径，不然守护模式运行会有问题
        $tempDir = Config::getInstance()->getConf('TEMP_DIR');
        if(empty($tempDir)){
            $tempDir = EASYSWOOLE_ROOT.'/Temp';
            Config::getInstance()->setConf('TEMP_DIR',$tempDir);
        }else{
            $tempDir = rtrim($tempDir,'/');
        }
        if(!is_dir($tempDir)){
            mkdir($tempDir);
        }
        defined('EASYSWOOLE_TEMP_DIR') or define('EASYSWOOLE_TEMP_DIR',$tempDir);

        $logDir = Config::getInstance()->getConf('LOG_DIR');
        if(empty($logDir)){
            $logDir = EASYSWOOLE_ROOT.'/Log';
            Config::getInstance()->setConf('LOG_DIR',$logDir);
        }else{
            $logDir = rtrim($logDir,'/');
        }
        if(!is_dir($logDir)){
            mkdir($logDir);
        }
        defined('EASYSWOOLE_LOG_DIR') or define('EASYSWOOLE_LOG_DIR',$logDir);

        //设置默认文件目录值
        Config::getInstance()->setConf('MAIN_SERVER.SETTING.pid_file',$tempDir.'/pid.pid');
        Config::getInstance()->setConf('MAIN_SERVER.SETTING.log_file',$logDir.'/swoole.log');
    }

    private function registerErrorHandler()
    {
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);

        //初始化配置Logger
        $logger = Di::getInstance()->get(SysConst::LOGGER_HANDLER);
        if(!$logger instanceof LoggerInterface){
            $logger = new \EasySwoole\Trace\Logger(EASYSWOOLE_LOG_DIR);
        }
        Logger::getInstance($logger);

        //初始化追追踪器
        $trigger = Di::getInstance()->get(SysConst::TRIGGER_HANDLER);
        if(!$trigger instanceof TriggerInterface){
            /*
             * DISPLAY_ERROR
             */
            $display = Config::getInstance()->getConf('DISPLAY_ERROR');
            $trigger = new \EasySwoole\Trace\Trigger(Logger::getInstance(),$display);
        }
        Trigger::getInstance($trigger);

        //在没有配置自定义错误处理器的情况下，转化为trigger处理
        $errorHandler = Di::getInstance()->get(SysConst::ERROR_HANDLER);
        if(!is_callable($errorHandler)){
            $errorHandler = function($errorCode, $description, $file = null, $line = null){
                $l = new Location();
                $l->setFile($file);
                $l->setLine($line);
                Trigger::getInstance()->error($description,$errorCode,$l);
            };
        }
        set_error_handler($errorHandler);

        $func = Di::getInstance()->get(SysConst::SHUTDOWN_FUNCTION);
        if(!is_callable($func)){
            $func = function (){
                $error = error_get_last();
                if(!empty($error)){
                    $l = new Location();
                    $l->setFile($error['file']);
                    $l->setLine($error['line']);
                    Trigger::getInstance()->error($error['message'],$error['type'],$l);
                }
            };
        }
        register_shutdown_function($func);
    }

    private function registerDefaultCallBack(\swoole_server $server,int $serverType)
    {
        /*
         * 注册默认回调
         */
        if($serverType !== EASYSWOOLE_SERVER){
            $namespace = Di::getInstance()->get(SysConst::HTTP_CONTROLLER_NAMESPACE);
            if(empty($namespace)){
                $namespace = 'App\\HttpController\\';
            }
            $depth = intval(Di::getInstance()->get(SysConst::HTTP_CONTROLLER_MAX_DEPTH));
            $depth = $depth > 5 ? $depth : 5;
            $max = intval(Di::getInstance()->get(SysConst::HTTP_CONTROLLER_POOL_MAX_NUM));
            if($max == 0){
                $max = 15;
            }
            $waitTime = intval(Di::getInstance()->get(SysConst::HTTP_CONTROLLER_POOL_WAIT_TIME));
            if($waitTime == 0){
                $waitTime = 5;
            }
            $dispatcher = new Dispatcher($namespace,$depth,$max);
            $dispatcher->setControllerPoolWaitTime($waitTime);
            $httpExceptionHandler = Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER);
            if(!is_callable($httpExceptionHandler)){
                $httpExceptionHandler = function ($throwable,$request,$response){
                    $response->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);
                    $response->write(nl2br($throwable->getMessage()."\n".$throwable->getTraceAsString()));
                    Trigger::getInstance()->throwable($throwable);
                };
                Di::getInstance()->set(SysConst::HTTP_EXCEPTION_HANDLER,$httpExceptionHandler);
            }
            $dispatcher->setHttpExceptionHandler($httpExceptionHandler);

            EventHelper::on($server,EventRegister::onRequest,function (\swoole_http_request $request,\swoole_http_response $response)use($dispatcher){
                $request_psr = new Request($request);
                $response_psr = new Response($response);
                try{
                    if(EasySwooleEvent::onRequest($request_psr,$response_psr)){
                        $dispatcher->dispatch($request_psr,$response_psr);
                    }
                }catch (\Throwable $throwable){
                    call_user_func(Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER),$throwable,$request_psr,$response_psr);
                }finally{
                    try{
                        EasySwooleEvent::afterRequest($request_psr,$response_psr);
                    }catch (\Throwable $throwable){
                        call_user_func(Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER),$throwable,$request_psr,$response_psr);
                    }
                }
                $response_psr->__response();
            });
        }
        //注册默认的on task,finish  不经过 event register。因为on task需要返回值。不建议重写onTask,否则es自带的异步任务事件失效
        //其次finish逻辑在同进程中实现、
        if(Config::getInstance()->getConf('MAIN_SERVER.SETTING.task_enable_coroutine')){
            EventHelper::on($server,EventRegister::onTask,function (\swoole_server $server, Task $task){
                $taskObj = $task->data;
                if(is_string($taskObj) && class_exists($taskObj)){
                    $ref = new \ReflectionClass($taskObj);
                    if($ref->implementsInterface(QuickTaskInterface::class)){
                        try{
                            $taskObj::run($server,$task->id,$task->worker_id,$task->flags);
                        }catch (\Throwable $throwable){
                            Trigger::getInstance()->throwable($throwable);
                        }
                        return;
                    }else if($ref->isSubclassOf(AbstractAsyncTask::class)){
                        $taskObj = new $taskObj;
                    }
                }
                if($taskObj instanceof AbstractAsyncTask){
                    try{
                        $ret = $taskObj->__onTaskHook($task->id,$task->worker_id,$task->flags);
                        if($ret !== null){
                            $taskObj->__onFinishHook($ret,$task->id);
                        }
                    }catch (\Throwable $throwable){
                        Trigger::getInstance()->throwable($throwable);
                    }
                }else if($taskObj instanceof SuperClosure){
                    try{
                        return $taskObj( $server, $task->id,$task->worker_id,$task->flags);
                    }catch (\Throwable $throwable){
                        Trigger::getInstance()->throwable($throwable);
                    }
                }else if(is_callable($taskObj)){
                    try{
                        call_user_func($taskObj,$server,$task->id,$task->worker_id,$task->flags);
                    }catch (\Throwable $throwable){
                        Trigger::getInstance()->throwable($throwable);
                    }
                }
                return null;
            });
        }else{
            EventHelper::on($server,EventRegister::onTask,function (\swoole_server $server, $taskId, $fromWorkerId,$taskObj){
                if(is_string($taskObj) && class_exists($taskObj)){
                    $ref = new \ReflectionClass($taskObj);
                    if($ref->implementsInterface(QuickTaskInterface::class)){
                        try{
                            $taskObj::run($server,$taskId,$fromWorkerId);
                        }catch (\Throwable $throwable){
                            Trigger::getInstance()->throwable($throwable);
                        }
                        return;
                    }else if($ref->isSubclassOf(AbstractAsyncTask::class)){
                        $taskObj = new $taskObj;
                    }
                }
                if($taskObj instanceof AbstractAsyncTask){
                    try{
                        $ret = $taskObj->__onTaskHook($taskId,$fromWorkerId);
                        if($ret !== null){
                            $taskObj->__onFinishHook($ret,$taskId);
                        }
                    }catch (\Throwable $throwable){
                        Trigger::getInstance()->throwable($throwable);
                    }
                }else if($taskObj instanceof SuperClosure){
                    try{
                        return $taskObj( $server, $taskId, $fromWorkerId);
                    }catch (\Throwable $throwable){
                        Trigger::getInstance()->throwable($throwable);
                    }
                }else if(is_callable($taskObj)){
                    try{
                        call_user_func($taskObj,$server,$taskId,$fromWorkerId);
                    }catch (\Throwable $throwable){
                        Trigger::getInstance()->throwable($throwable);
                    }
                }
                return null;
            });
        }

        EventHelper::on($server,EventRegister::onFinish,function (){
            //空逻辑
        });

        //注册默认的worker start
        EventHelper::registerWithAdd(ServerManager::getInstance()->getMainEventRegister(),EventRegister::onWorkerStart,function (\swoole_server $server,$workerId){
            if(PHP_OS != 'Darwin'){
                $name = Config::getInstance()->getConf('SERVER_NAME');
                if( ($workerId < Config::getInstance()->getConf('MAIN_SERVER.SETTING.worker_num')) && $workerId >= 0){
                    $type = 'Worker';
                }else{
                    $type = 'TaskWorker';
                }
                cli_set_process_title("{$name}.{$type}.{$workerId}");
            }
        });
    }

    public function loadEnv()
    {
        //加载之前，先清空原来的
        if($this->isDev){
            $file  = EASYSWOOLE_ROOT.'/dev.php';
        }else{
            $file  = EASYSWOOLE_ROOT.'/produce.php';
        }
        Config::getInstance()->loadEnv($file);
    }

    private function extraHandler()
    {
        $serverName = Config::getInstance()->getConf('SERVER_NAME');

        //注册Console
        if(Config::getInstance()->getConf('CONSOLE.ENABLE')){
            $config = Config::getInstance()->getConf('CONSOLE');
            ServerManager::getInstance()->addServer('CONSOLE',$config['PORT'],SWOOLE_TCP,$config['LISTEN_ADDRESS']);
            Console::getInstance()->attachServer(ServerManager::getInstance()->getSwooleServer('CONSOLE'),new ConsoleConfig());
            Console::getInstance()->setServer(ServerManager::getInstance()->getSwooleServer());
            ServerManager::getInstance()->getSwooleServer('CONSOLE')->on('close',function (){
                Auth::$authTable->set(Config::getInstance()->getConf('CONSOLE.USER'),[
                    'fd'=>0
                ]);
            });
            ConsoleModuleContainer::getInstance()->set(new Auth());
            ConsoleModuleContainer ::getInstance()->set(new Server());
            ConsoleModuleContainer ::getInstance()->set(new Log());
        }
        //注册crontab进程
        Crontab::getInstance()->__run();
        //注册fastCache进程
        if(Config::getInstance()->getConf('FAST_CACHE.PROCESS_NUM') > 0){
            Cache::getInstance()->setTempDir(EASYSWOOLE_TEMP_DIR)
                ->setProcessNum(Config::getInstance()->getConf('FAST_CACHE.PROCESS_NUM'))
                ->setBacklog(Config::getInstance()->getConf('FAST_CACHE.BACKLOG'))
                ->setServerName($serverName)
                ->attachToServer(ServerManager::getInstance()->getSwooleServer());
        }
        //执行Actor注册进程
        Actor::getInstance()->setTempDir(EASYSWOOLE_TEMP_DIR)->setServerName($serverName)->attachToServer(ServerManager::getInstance()->getSwooleServer());

    }
}