<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/6
 * Time: 下午1:54
 */

namespace App;


use Core\AbstractInterface\AbstractAsyncTask;
use Core\Component\Logger;

class Task extends AbstractAsyncTask
{

    function handler(\swoole_http_server $server, $taskId, $fromId)
    {
        // TODO: Implement handler() method.
       return "AAA";
    }

    function finishCallBack(\swoole_http_server $server, $task_id, $resultData)
    {
        // TODO: Implement finishCallBack() method.
        Logger::getInstance()->console("task finish callback with data {$resultData}",false);
    }
}