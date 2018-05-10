<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/3/8
 * Time: 下午2:03
 */

namespace EasySwoole\Core\Swoole;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\SuperClosure;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Http\AbstractInterface\ExceptionHandlerInterface;
use EasySwoole\Core\Http\Dispatcher;
use EasySwoole\Core\Http\Message\Status;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;
use EasySwoole\Core\Socket\Dispatcher as SocketDispatcher;
use EasySwoole\Core\Swoole\PipeMessage\Message;
use EasySwoole\Core\Swoole\Task\AbstractAsyncTask;
use \EasySwoole\Core\Swoole\PipeMessage\EventRegister as PipeMessageEventRegister;
use EasySwoole\EasySwooleEvent;

class EventHelper
{

    public static function register(EventRegister $register,string $event,callable $callback):void
    {
        $register->set($event,$callback);
    }

    public static function registerWithAdd(EventRegister $register,string $event,callable $callback):void
    {
        $register->add($event,$callback);
    }

    public static function registerDefaultOnRequest(EventRegister $register,$controllerNameSpace = 'App\\HttpController\\'):void
    {
        $dispatcher = new Dispatcher($controllerNameSpace);
        $register->set($register::onRequest,function (\swoole_http_request $request,\swoole_http_response $response)use($dispatcher){
            $request_psr = new Request($request);
            $response_psr = new Response($response);
            try{
                EasySwooleEvent::onRequest($request_psr,$response_psr);
                $dispatcher->dispatch($request_psr,$response_psr);
                EasySwooleEvent::afterAction($request_psr,$response_psr);
            }catch (\Throwable $throwable){
                $handler = Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER);
                if($handler instanceof ExceptionHandlerInterface){
                    $handler->handle($throwable,$request_psr,$response_psr);
                }else{
                    $response_psr->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);
                    $response_psr->write(nl2br($throwable->getMessage() ."\n". $throwable->getTraceAsString()));
                }
            }
            $response_psr->response();
        });
    }



    public static function registerDefaultOnTask(EventRegister $register):void
    {
        $register->set($register::onTask,function (\swoole_server $server, $taskId, $fromWorkerId,$taskObj)
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
                    return $taskObj( $server, $taskId, $fromWorkerId);
                }catch (\Throwable $throwable){
                    Trigger::throwable($throwable);
                }
            }
            return null;
        });
    }

    public static function registerDefaultOnFinish(EventRegister $register):void
    {
        $register->set($register::onFinish,function (\swoole_server $server, $taskId, $taskObj)
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

    public static function registerDefaultOnReceive(EventRegister $register,string $parserInterface,callable $onError = null,string $exceptionHandler = null):void
    {
        $dispatch = new SocketDispatcher($parserInterface);
        $dispatch->setErrorHandler($onError);
        $dispatch->setExceptionHandler($exceptionHandler);
        $register->add($register::onReceive,function (\swoole_server $server, int $fd, int $reactor_id, string $data)use($dispatch){
            $dispatch->dispatch($dispatch::TCP,$data,$fd,$reactor_id);
        });
    }

    public static function registerDefaultOnPacket(EventRegister $register,string $parserInterface,callable $onError = null,string $exceptionHandler = null)
    {
        $dispatch = new SocketDispatcher($parserInterface);
        $dispatch->setErrorHandler($onError);
        $dispatch->setExceptionHandler($exceptionHandler);
        $register->set($register::onPacket,function (\swoole_server $server, string $data, array $client_info)use($dispatch){
            $dispatch->dispatch($dispatch::UDP,$data,$client_info);
        });
    }

    public static function registerDefaultOnMessage(EventRegister $register,string $parserInterface,callable $onError = null,string $exceptionHandler = null)
    {
        $dispatch = new SocketDispatcher($parserInterface);
        $dispatch->setErrorHandler($onError);
        $dispatch->setExceptionHandler($exceptionHandler);
        $register->set($register::onMessage,function (\swoole_server $server, \swoole_websocket_frame $frame)use($dispatch){
            $dispatch->dispatch($dispatch::WEB_SOCK,$frame->data,$frame);
        });
    }

    public static function registerDefaultOnPipeMessage(EventRegister $register):void
    {
        $register->set($register::onPipeMessage,function (\swoole_server $server,$fromWorkerId,$data){
            $message = \swoole_serialize::unpack($data);
            if($message instanceof Message){
                PipeMessageEventRegister::getInstance()->hook($message->getCommand(),$fromWorkerId,$message->getData());
            }else{
                Trigger::error("data :{$data} not packet by swoole_serialize or not a Message Instance");
            }
        });
    }
}