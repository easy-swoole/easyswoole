<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午2:45
 */

namespace EasySwoole\Core\Swoole;


use EasySwoole\Core\Component\Container;

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

    private $allows = [
        'start','shutdown','workerStart','workerStop','workerExit','timer',
        'connect','receive','packet','close','bufferFull','bufferEmpty','task',
        'finish','pipeMessage','workerError','managerStart','managerStop'
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
}