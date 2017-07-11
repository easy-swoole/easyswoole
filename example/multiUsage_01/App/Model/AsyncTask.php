<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/7
 * Time: 下午11:49
 */

namespace App\Model;


use Core\AbstractInterface\AbstractAsyncTask;
use Core\Component\Logger;

class AsyncTask extends AbstractAsyncTask
{

    function handler(\swoole_http_server $server, $taskId, $fromId)
    {
        // TODO: Implement handler() method.
        Logger::console("this is async task runner");
        $this->finish(time());
    }

    function finishCallBack(\swoole_http_server $server, $task_id, $resultData)
    {
        // TODO: Implement finishCallBack() method.
        Logger::console("this is async task runner callback with data {$resultData}");
    }
}