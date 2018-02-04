<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午2:45
 */

namespace EasySwoole\Core\Swoole;

use EasySwoole\Core\Component\Container;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\Event;
use EasySwoole\Core\Component\SuperClosure;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Http\AbstractInterface\ExceptionHandlerInterface;
use EasySwoole\Core\Http\Dispatcher;
use EasySwoole\Core\Http\Message\Status;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;
use EasySwoole\Core\Socket\AbstractInterface\ExceptionHandler;
use EasySwoole\Core\Socket\AbstractInterface\ParserInterface;
use EasySwoole\Core\Socket\Dispatcher as SocketDispatcher;
use EasySwoole\Core\Swoole\Task\AbstractAsyncTask;


class EventRegister
{
    const onStart = 'start';
    const onShutdown = 'shutdown';
    const onWorkerStart = 'workerStart';
    const onWorkerStop = 'workerStop';
    const onWorkerExit = 'workerExit';
    const onTimer = 'timer';
    const onConnect = 'connect';
    const onReceive = 'receive';
    const onPacket = 'packet';
    const onClose = 'close';
    const onBufferFull = 'bufferFull';
    const onBufferEmpty = 'bufferEmpty';
    const onTask = 'task';
    const onFinish = 'finish';
    const onPipeMessage = 'pipeMessage';
    const onWorkerError = 'workerError';
    const onManagerStart = 'managerStart';
    const onManagerStop = 'managerStop';
    const onRequest = 'request';
    const onHandShake = 'handShake';
    const onMessage = 'message';
    const onOpen = 'open';

    private $allows = [
        'start','shutdown','workerStart','workerStop','workerExit','timer',
        'connect','receive','packet','close','bufferFull','bufferEmpty','task',
        'finish','pipeMessage','workerError','managerStart','managerStop',
        'request','handShake','message','open'
    ];

    private $eventList = [];

    function add($key, $item): EventRegister
    {
        if(in_array($key,$this->allows)){
            if(is_callable($item)){
                $this->eventList[$key] = [$item];
            }else{
                trigger_error("event {$key} is not a callable");
            }
        }else{
            trigger_error("event {$key} is not allow");
        }
        return $this;
    }

    public function get($key)
    {
        if (isset($this->eventList[$key])) {
            return $this->eventList[$key];
        } else {
            return null;
        }
    }

    function withAdd($key, $item)
    {
        if(in_array($key,$this->allows)){
            if(is_callable($item)){
                if (isset($this->eventList[$key])) {
                    $old = $this->eventList[$key];
                }else{
                    $old = [];
                }
                $old[] = $item;
                $this->eventList[$key] = $old;
            }else{
                trigger_error("event {$key} is not a callable");
            }
        }else{
            trigger_error("event {$key} is not allow");
        }
        return $this;
    }

    function all(): array
    {
        return $this->eventList;
    }

    public function registerDefaultOnRequest($controllerNameSpace = 'App\\HttpController\\'):void
    {
        $dispatcher = new Dispatcher($controllerNameSpace);
        $this->add(self::onRequest,function (\swoole_http_request $request,\swoole_http_response $response)use($dispatcher){
            $request_psr = new Request($request);
            $response_psr = new Response($response);
            try{
                $event = Event::getInstance();
                $event->hook('onRequest',$request_psr,$response_psr);
                $dispatcher->dispatch($request_psr,$response_psr);
                $event->hook('afterAction',$request_psr,$response_psr);
            }catch (\Throwable $throwable){
                $handler = Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER);
                if($handler instanceof ExceptionHandlerInterface){
                    $handler->handle($throwable,$request_psr,$response_psr);
                }else{
                    $response_psr->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);
                    $response_psr->write($throwable->getMessage() . $throwable->getTraceAsString());
                }
            }
            //携程模式下  底层不会自动end
            if($response_psr->autoResponse()){
                $response_psr->response();
            }
            if($response_psr->autoEnd()){
                $response_psr->end(true);
            }
        });
    }

    public function registerDefaultOnTask():void
    {
        $this->add(self::onTask,function (\swoole_server $server, $taskId, $fromWorkerId,$taskObj)
        {
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
                    return $taskObj();
                }catch (\Throwable $throwable){
                    trigger_error($throwable->getMessage());
                }
            }
            return null;
        });
    }

    public function registerDefaultOnFinish():void
    {
        $this->add(self::onFinish,function (\swoole_server $server, $taskId, $taskObj)
        {
            //finish 在仅仅对AbstractAsyncTask做处理，其余处理无意义。
            if($taskObj instanceof AbstractAsyncTask){
                try{
                    $taskObj->finish($taskObj->getResult(),$taskId);
                }catch (\Throwable $throwable){
                    $taskObj->onException($throwable);
                }
            }
        });
    }

    public function registerDefaultOnReceive(ParserInterface $parser,callable $onError = null,ExceptionHandler $exceptionHandler = null):void
    {
        $dispatch = new SocketDispatcher($parser);
        $dispatch->onError($onError);
        $dispatch->setExceptionHandler($exceptionHandler);
        $this->add(self::onReceive,function (\swoole_server $server, int $fd, int $reactor_id, string $data)use($dispatch){
            $dispatch->dispatch($dispatch::TCP,$data,$fd,$reactor_id);
        });
    }

    public function registerDefaultOnPacket(ParserInterface $parser,callable $onError = null,ExceptionHandler $exceptionHandler = null)
    {
        $dispatch = new SocketDispatcher($parser);
        $dispatch->onError($onError);
        $dispatch->setExceptionHandler($exceptionHandler);
        $this->add(self::onPacket,function (\swoole_server $server, string $data, array $client_info)use($dispatch){
            $dispatch->dispatch($dispatch::UDP,$data,$client_info);
        });
    }

    public function registerDefaultOnMessage(ParserInterface $parser,callable $onError = null,ExceptionHandler $exceptionHandler = null)
    {
        $dispatch = new SocketDispatcher($parser);
        $dispatch->onError($onError);
        $dispatch->setExceptionHandler($exceptionHandler);
        $this->add(self::onMessage,function (\swoole_server $server, \swoole_websocket_frame $frame)use($dispatch){
            $dispatch->dispatch($dispatch::WEB_SOCK,$frame->data,$frame);
        });
    }
}