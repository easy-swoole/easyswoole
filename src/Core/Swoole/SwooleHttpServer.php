<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/22
 * Time: 下午9:55
 */

namespace Core\Swoole;


use Conf\Event;
use Core\AbstractInterface\AbstractAsyncTask;
use Core\Component\SuperClosure;
use Core\Dispatcher;
use Core\Http\Request;
use Core\Http\Response;

class SwooleHttpServer
{
    protected static $instance;
    protected $swooleServer;
    protected $isStart = 0;
    /*
     * 仅仅用于获取一个服务实例
     * @return SwooleHttpServer
     */
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }

    function __construct()
    {
        if(Config::getInstance()->wsSupport()){
            $this->swooleServer = new \swoole_websocket_server(Config::getInstance()->listenIp(),Config::getInstance()->listenPort());
        }else{
            $this->swooleServer = new \swoole_http_server(Config::getInstance()->listenIp(),Config::getInstance()->listenPort());
        }
    }

    function isStart(){
        return $this->isStart;
    }
    /*
     * 创建并启动一个swoole http server
     */
    function startServer(){
        $this->getServer()->set(Config::getInstance()->workerSetting());
        $this->beforeWorkerStart();
        $this->serverStartEvent();
        $this->serverShutdownEvent();
        $this->workerErrorEvent();
        $this->onTaskEvent();
        $this->onFinish();
        $this->workerStartEvent();
        $this->workerStopEvent();
        $this->listenRequest();
        $this->isStart = 1;
        $this->getServer()->start();
    }
    /*
     * 用于获取 swoole_http_server 实例
     * server启动后，在每个进程中获得的，均为当前自身worker的server（可以理解为进程克隆后独立运行）
     * @return swoole_http_server
     */
    function getServer(){
        return $this->swooleServer;
    }
    /*
     * 监听http请求
     */
    private function listenRequest(){
        $this->getServer()->on("request",
            function (\swoole_http_request $request,\swoole_http_response $response){
            Request::getInstance($request);
            Response::getInstance($response);
            Event::getInstance()->onRequest(Request::getInstance(),Response::getInstance());
            Dispatcher::getInstance()->dispatch();
            Event::getInstance()->afterResponse(Request::getInstance());
            Response::getInstance()->end();
        });
    }
    private function workerStartEvent(){
        $this->getServer()->on("workerStart",function (\swoole_server $server, $workerId){
            Event::getInstance()->onWorkerStart($server,$workerId);
        });
    }
    private function workerStopEvent(){
        $this->getServer()->on("workerStop",function (\swoole_server $server, $workerId){
            Event::getInstance()->onWorkerStop($server,$workerId);
        });
    }
    private function onTaskEvent(){
        $num = Config::getInstance()->allTaskWorkerNum();
        if(!empty($num)){
            $this->getServer()->on("task",function (\swoole_http_server $server, $taskId, $fromId,$taskObj){
                Event::getInstance()->onTask($server, $taskId, $fromId,$taskObj);
                if($taskObj instanceof AbstractAsyncTask){
                    return $taskObj->handler($server, $taskId, $fromId,$taskObj->taskData());
                }else if($taskObj instanceof SuperClosure){
                    return $taskObj($server, $taskId);
                }
            });
        }
    }
    private function onFinish(){
        $num = Config::getInstance()->allTaskWorkerNum();
        if(!empty($num)){
            $this->getServer()->on("finish",
                function (\swoole_http_server $server, $taskId,$obj){
                    Event::getInstance()->onFinish($server, $taskId, $taskId,$obj);
                    //非AbstractAsyncTask对象无执行回调需求
                    if($obj instanceof AbstractAsyncTask){
                        $taskClassName = $obj['taskClassName'];
                        $reflection = new \ReflectionClass ( $taskClassName );
                        if($reflection){
                            $instance = $reflection->newInstance();
                            if($instance instanceof AbstractAsyncTask){
                                $instance->taskData($obj['taskData']);
                                $instance->taskResultData($obj['taskResultData']);
                                $instance->finishCallBack($server, $taskId,$obj['taskResultData']);
                            }
                            unset($instance);
                        }
                        unset($reflection);
                    }
                }
            );
        }
    }
    private function beforeWorkerStart(){
        Event::getInstance()->beforeWorkerStart($this->getServer());
    }
    private function serverStartEvent(){
        $this->getServer()->on("start",function (\swoole_http_server $server){
            Event::getInstance()->onStart($server);
        });
    }
    private function serverShutdownEvent(){
        $this->getServer()->on("shutdown",function (\swoole_http_server $server){
            Event::getInstance()->onShutdown($server);
        });
    }
    /*
     * 当worker/task_worker进程发生异常后会在Manager进程内回调此函数。
        $worker_id是异常进程的编号
        $worker_pid是异常进程的ID
        $exit_code退出的状态码，范围是 1 ～255
        此函数主要用于报警和监控，一旦发现Worker进程异常退出，那么很有可能是遇到了致命错误或者进程CoreDump。
        通过记录日志或者发送报警的信息来提示开发者进行相应的处理。
     */
    private function workerErrorEvent(){
        $this->getServer()->on("workererror",function (\swoole_http_server $server,$worker_id, $worker_pid, $exit_code){
            Event::getInstance()->onWorkerError($server, $worker_id, $worker_pid, $exit_code);
        });
    }
}