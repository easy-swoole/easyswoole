<?php

/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/3/19
 * Time: 上午11:29
 */
namespace App;
use Core\Component\Logger;
use \Core\AbstractInterface\AbstractAsyncTask;

class AsyncTask extends AbstractAsyncTask
{
    function handler(\swoole_http_server $server, $taskId, $fromId)
    {
        // TODO: Implement handler() method.
        Logger::console("this is my async task");
        $this->doFinish("async data");
    }

    function finishCallBack(\swoole_http_server $server, $task_id, $resultData)
    {
        // TODO: Implement finishCallBack() method.
        Logger::console("async end");
    }

}