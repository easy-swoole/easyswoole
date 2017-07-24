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
        /*
        HTTP/1.1 200 OK
        Date: Mon, 24 Jul 2017 06:41:52 GMT
        Server: Apache/2.4.25 (Unix) PHP/5.6.30
        Last-Modified: Mon, 17 Jul 2017 03:47:23 GMT
        ETag: "18d6-5547b41ab28c0"
        Accept-Ranges: bytes
        Content-Length: 6358
        Content-Type: text/html
         */
        $status = $response->getStatusCode();
        $reason = $response->getReasonPhrase();
        $str = "HTTP/1.1 {$status} {$reason}\n";
        $headers = $response->getHeaders();
        foreach ($headers as $header => $val){
            foreach ($val as $sub){
                $str .= "{$header}: {$sub}\n";
            }
        }
        $str .= $response->getBody();

        Logger::console($str);

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
