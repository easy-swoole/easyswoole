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
        date_default_timezone_set('Asia/Shanghai');
    }

    function beforeWorkerStart(\swoole_http_server $server)
    {
        // TODO: Implement beforeWorkerStart() method.
        $listener = $server->addlistener("0.0.0.0",9502,SWOOLE_TCP);
        //混合监听tcp时    要重新设置包解析规则  才不会被HTTP覆盖，且端口不能与HTTP SERVER一致 HTTP本身就是TCP
        $listener->set(array(
            "open_eof_check"=>false,
            "package_max_length"=>2048,
        ));
        $listener->on("connect",function(\swoole_server $server,$fd){
            Logger::getInstance()->console("client connect");
        });
        $listener->on("receive",function(\swoole_server $server,$fd,$from_id,$data){
            Logger::getInstance()->console("received data :".$data);
            $server->send($fd,"swoole ".$data);
        });
        $listener->on("close",function (\swoole_server $server,$fd){
            Logger::getInstance()->console("client close");
        });
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
