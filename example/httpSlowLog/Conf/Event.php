<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:06
 */

namespace Conf;


use App\Utility\Mysqli;
use App\Utility\SysConst;
use Core\AbstractInterface\AbstractEvent;
use Core\AutoLoader;
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
        $request->withAttribute("startTime",microtime(true));
    }

    function onDispatcher(Request $request, Response $response, $targetControllerClass, $targetAction)
    {
        // TODO: Implement onDispatcher() method.
    }

    function onResponse(Request $request,Response $response)
    {
        // TODO: Implement afterResponse() method.
        $end = microtime(true);
        $ret = $end - $request->getAttribute("startTime");
        Logger::console("your request take {$ret}");
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
