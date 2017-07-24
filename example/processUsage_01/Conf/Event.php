<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:06
 */

namespace Conf;


use Core\AbstractInterface\AbstractEvent;
use Core\Component\Di;
use Core\Component\Logger;
use Core\Component\Version\Control;
use Core\Http\Request;
use Core\Http\Response;

class Event extends AbstractEvent
{
    function frameInitialize()
    {
        // TODO: Implement frameInitialize() method.

    }

    function beforeWorkerStart(\swoole_http_server $server)
    {
        // TODO: Implement beforeWorkerStart() method.
        //添加自定义进程
        $server->addProcess(new \swoole_process(function (){
            while (1){
                /*
                 * for example,you can set a redis,kafka or rabbitMQ client
                 * here,and read the message queue in blocking mode
                */
                sleep(1);
                Logger::console("my process run");
            }
        }));
    }

    function onStart(\swoole_http_server $server)
    {
        // TODO: Implement onStart() method.

    }

    function onShutdown(\swoole_http_server $server)
    {
        // TODO: Implement onShutdown() method.
    }

    function onWorkerStart(\swoole_server $server, $workerId)
    {
        // TODO: Implement onWorkerStart() method.
    }

    function onWorkerStop(\swoole_server $server, $workerId)
    {
        // TODO: Implement onWorkerStop() method.
    }

    function onRequest(Request $request, Response $response)
    {
        // TODO: Implement onRequest() method.
    }

    function onDispatcher(Request $request, Response $response, $targetControllerClass, $targetAction)
    {
        // TODO: Implement onDispatcher() method.
    }

    function onResponse(Request $request,Response $response)
    {
        // TODO: Implement afterResponse() method.
    }

    function onTask(\swoole_http_server $server, $taskId, $fromId, $taskObj)
    {
        // TODO: Implement onTask() method.
    }

    function onFinish(\swoole_http_server $server, $taskId, $fromId, $taskObj)
    {
        // TODO: Implement onFinish() method.
    }

    function onWorkerError(\swoole_http_server $server, $worker_id, $worker_pid, $exit_code)
    {
        // TODO: Implement onWorkerError() method.
    }
}
