<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:06
 */

namespace Conf;


use App\Model\Runner;
use App\Utility\SysConst;
use Core\AbstractInterface\AbstractEvent;
use Core\Component\Di;
use Core\Component\Logger;
use Core\Component\ShareMemory;
use Core\Component\Version\Control;
use Core\Http\Request;
use Core\Http\Response;
use Core\Swoole\AsyncTaskManager;
use Core\Swoole\Timer;

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
        //其他初始化的时候  清除共享信息
        ShareMemory::getInstance()->clear();
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
        //为第一个进程添加唤起任务执行的定时器；
        if($workerId == 0){
            Timer::loop(1000,function (){
                $share = ShareMemory::getInstance();
                //请勿使得所有worker全部处于繁忙状态   危险操作
                if($share->get(SysConst::TASK_RUNNING_NUM) < 2){
                    AsyncTaskManager::getInstance()->add(Runner::class);
                }
            });
        }
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
