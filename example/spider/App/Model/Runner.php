<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/8/9
 * Time: 下午3:38
 */

namespace App\Model;


use App\Utility\SysConst;
use Core\AbstractInterface\AbstractAsyncTask;
use Core\Component\Logger;
use Core\Component\ShareMemory;
use Core\Utility\Curl\Request;

class Runner extends AbstractAsyncTask
{

    function handler(\swoole_http_server $server, $taskId, $fromId)
    {
        // TODO: Implement handler() method.
        //记录处于运行状态的task数量
        $share = ShareMemory::getInstance();
        $share->startTransaction();
        $share->set(SysConst::TASK_RUNNING_NUM,$share->get(SysConst::TASK_RUNNING_NUM)+1);
        $share->commit();
        //while其实为危险操作，while会剥夺进程控制权
        while (true){
            $task = Queue::pop();
            if($task instanceof TaskBean){
                $req = new Request($task->getUrl());
                $ret = $req->exec()->getBody();
                Logger::getInstance("curl")->console("finish url:".$task->getUrl());
            }else{
                break;
            }
        }
//        Logger::getInstance()->console("async task exit");
        $share->startTransaction();
        $share->set(SysConst::TASK_RUNNING_NUM,$share->get(SysConst::TASK_RUNNING_NUM)-1);
        $share->commit();
    }

    function finishCallBack(\swoole_http_server $server, $task_id, $resultData)
    {
        // TODO: Implement finishCallBack() method.
    }
}