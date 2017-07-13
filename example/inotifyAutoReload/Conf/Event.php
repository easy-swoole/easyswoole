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
use Core\Component\ShareMemory;
use Core\Component\Version\Control;
use Core\Http\Request;
use Core\Http\Response;
use Core\Swoole\SwooleHttpServer;

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
        ShareMemory::getInstance()->clear();
    }

    function onStart(\swoole_http_server $server)
    {
        // TODO: Implement onStart() method.
        //请确定有inotify拓展
        $a = function ($dir)use(&$a){
            $data = array();
            if(is_dir($dir)){
                //是目录的话，先增当前目录进去
                $data[] = $dir;
                $files = array_diff(scandir($dir), array('.', '..'));
                foreach ($files as $file){
                    $data = array_merge($data ,$a($dir."/".$file));
                }
            }else{
                $data[] = $dir;
            }
            return $data;
        };
        $list = $a(ROOT."/App");

        $notify = inotify_init();
        foreach ($list as $item){
            inotify_add_watch($notify, $item,IN_CREATE | IN_DELETE|IN_MODIFY);
        }
        swoole_event_add($notify,function()use($notify){
            $events = inotify_read($notify);
            if(!empty($events)){
                //注意更新多个文件的间隔时间处理,防止一次更新了10个文件，重启了10次，懒得做了，反正原理在这里
                SwooleHttpServer::getInstance()->getServer()->reload();
            }
        });
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
