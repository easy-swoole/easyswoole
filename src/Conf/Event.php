<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:06
 */

namespace Conf;


use Core\AbstractInterface\AbstractEvent;
use Core\AutoLoader;
use Core\Component\Logger;
use Core\Http\Request;
use Core\Http\Response;
use Core\Swoole\SwooleHttpServer;
use Swoole\Timer;

class Event extends AbstractEvent
{
    function frameInitialize()
    {
        // TODO: Implement frameInitialize() method.
    }

    function beforeWorkerStart(\swoole_http_server $server)
    {
        // TODO: Implement beforeWorkerStart() method.
        $udp = $server->addlistener("0.0.0.0",9502,SWOOLE_UDP);
        $udp->on('receive',function(\swoole_server $server, $clientIp, $clientPort, $data){
            var_dump($data);
            var_dump(long2ip($clientIp));
            var_dump($clientIp);
            var_dump($clientPort);

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

    function afterResponse(Request $request)
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

    function onWorkerFatalError(\swoole_http_server $server)
    {
        // TODO: Implement onWorkerFatalError() method.
    }

}