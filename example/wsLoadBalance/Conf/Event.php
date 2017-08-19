<?php
/**
 * Created by PhpStorm.
 * User: liu
 * Date: 16/8/10
 * Time: 15:00
 */

namespace Conf;


use App\Utility\Redis;
use App\Utility\WebSocket;
use Core\AbstractInterface\AbstractEvent;
use Core\AutoLoader;
use Core\Component\Di;
use Core\Component\Logger;
use Core\Component\Version\Control;
use Core\Http\Request;
use Core\Http\Response;
use Core\Swoole\SwooleHttpServer;
use Core\Swoole\Timer;

class Event extends AbstractEvent {

    function frameInitialize() {
        // TODO: Implement frameInitialize() method.
        date_default_timezone_set('Asia/Shanghai');
        $loader = AutoLoader::getInstance();
        $loader->requireFile("App/Vendor/Smarty/Smarty.class.php");
    }

    function beforeWorkerStart(\swoole_http_server $server) {
        // TODO: Implement beforeWorkerStart() method.
        WebSocket::getInstance()->build($server);
    }

    function onStart(\swoole_http_server $server) {
        // TODO: Implement onStart() method.
    }

    function onShutdown(\swoole_http_server $server) {
        // TODO: Implement onShutdown() method.
    }

    function onWorkerStart(\swoole_server $server, $workerId) {
        // TODO: Implement onWorkerStart() method.
    }

    function onWorkerStop(\swoole_server $server, $workerId) {
        // TODO: Implement onWorkerStop() method.
    }

    function onRequest(Request $request, Response $response) {
        // TODO: Implement onRequest() method.
    }

    function onDispatcher(Request $request, Response $response, $targetControllerClass, $targetAction) {
        // TODO: Implement onDispatcher() method.
    }

    function onResponse(Request $request, Response $response) {
        // TODO: Implement afterResponse() method.
    }
    function onTask(\swoole_http_server $server, $taskId, $workerId, $taskObj)
    {
        // TODO: Implement onTask() method.
    }

    function onFinish(\swoole_http_server $server, $taskId, $taskObj)
    {
        // TODO: Implement onFinish() method.
    }

    function onWorkerError(\swoole_http_server $server, $worker_id, $worker_pid, $exit_code) {
        // TODO: Implement onWorkerError() method.
    }
}
