<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午2:45
 */

namespace EasySwoole\Core\Swoole;


use EasySwoole\Core\AbstractInterface\AbstractAsyncTask;
use EasySwoole\Core\AbstractInterface\HttpExceptionHandlerInterface;
use EasySwoole\Core\Component\Container;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\Logger;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Http\Dispatcher;
use EasySwoole\Core\Http\Message\Status;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;
use EasySwoole\Event;

class EventRegister extends Container
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

    function add($key, $item): Container
    {
        if(in_array($key,$this->allows)){
            if(is_callable($item)){
                parent::add($key, $item);
            }else{
                trigger_error("event {$key} is not a callable");
            }
        }else{
            trigger_error("event {$key} is not allow");
        }
        return $this;
    }

    public function registerDefaultOnRequest($appNameSpace = 'App\\'):void
    {
        $this->add(self::onRequest,function (\swoole_http_request $request,\swoole_http_response $response)use($appNameSpace){
            $request_psr = new Request($request);
            $response_psr = new Response($response);
            try{
                Event::onRequest($request_psr,$response_psr,$appNameSpace);
                Dispatcher::getInstance($appNameSpace)->dispatch($request_psr,$response_psr);
                Event::afterAction($request_psr,$response_psr,$appNameSpace);
            }catch (\Exception $exception){
                $handler = Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER);
                if($handler instanceof HttpExceptionHandlerInterface){
                    $handler->handle($exception,$request_psr,$response_psr);
                }else{
                    $response_psr = new Response($response);
                    $response_psr->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);
                    $response_psr->write($exception->getMessage());
                }
            }
            //携程模式下  底层不会自动end
            $response_psr->response();
        });
    }

    public function registerDefaultOnTask():void
    {
        $this->add(self::onTask,function (\swoole_server $server, $taskId, $fromWorkerId,$taskObj)
        {
            if(is_string($taskObj) && class_exists($taskObj)){
                $taskObj = new $taskObj;
            }
            try{
                if($taskObj instanceof AbstractAsyncTask){
                    $ret =  $taskObj->run($taskObj->getData(),$taskId,$fromWorkerId);
                    //在有return或者设置了结果的时候  说明需要执行结束回调
                    $ret = is_null($ret) ? $taskObj->getResult() : $ret;
                    if(!is_null($ret)){
                        $taskObj->setResult($ret);
                        return $taskObj;
                    }
                }
            }catch (\Exception $exception){

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
                }catch (\Exception $exception){

                }
            }
        });
    }
}