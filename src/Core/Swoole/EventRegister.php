<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午2:45
 */

namespace EasySwoole\Core\Swoole;


use EasySwoole\Core\Component\Container;
use EasySwoole\Core\Http\Dispatcher;
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

    function __construct($defaultEvents = true)
    {
        if($defaultEvents){
            $this->registerDefaultEvent();
        }
    }

    private function registerDefaultEvent()
    {
        if(Config::getInstance()->getServerType() != Config::TYPE_SERVER){
            $this->add(self::onRequest,function (\swoole_http_request $request,\swoole_http_response $response){
                $request_psr = new Request($request);
                $response_psr = new Response($response);
                Event::onRequest($request_psr,$response_psr);
                Dispatcher::getInstance()->dispatch($request_psr,$response_psr);
                Event::afterAction($request_psr,$response_psr);
                $response_psr->end(true);
            });
        }
        $this->add(self::onTask,function (\swoole_server $server, $taskId, $workerId,$taskObj){

        });
        $this->add(self::onFinish,function (\swoole_server $server, $taskId, $taskObj){

        });
    }
}