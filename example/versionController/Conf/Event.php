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
use Core\Component\Di;
use Core\Component\Logger;
use Core\Component\SysConst;
use Core\Component\Version\Control;
use Core\Http\Request;
use Core\Http\Response;

class Event extends AbstractEvent
{
    function frameInitialize()
    {
        // TODO: Implement frameInitialize() method.
        date_default_timezone_set('Asia/Shanghai');
        $versionControl = new Control();
        $versionControl->addVersion("v1",function (Request $request){
            if($request->getRequestParam("version") == 1){
                return true;
            }
        })->addPathMap("/test",function (Request $request,Response $response){
            $response->writeJson(200,"v1");
        });

        $versionControl->addVersion("v2",function (Request $request){
            if($request->getRequestParam("version") == 2){
                return true;
            }
        })->addPathMap("/test",function (Request $request,Response $response){
            $response->writeJson(200,"v2");
        })->addPathMap("/test2","/");

        Di::getInstance()->set(SysConst::VERSION_CONTROL,$versionControl);
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
        Di::getInstance()->get(SysConst::VERSION_CONTROL)->startControl();

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
