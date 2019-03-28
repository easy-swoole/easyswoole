<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/29
 * Time: 上午11:35
 */

namespace EasySwoole\EasySwoole\Swoole\Task;


abstract class AbstractAsyncTask
{
    private $data = null;

    final public function __construct($data = null)
    {
        $this->data = $data;
    }
    /*
     * if has return ,do finish call
     */
    function __onTaskHook($taskId,$fromWorkerId,$flags = null)
    {
        try{
            return $this->run($this->data,$taskId,$fromWorkerId,$flags);
        }catch (\Throwable $throwable){
            $this->onException($throwable);
        }
    }

    function __onFinishHook($finishData,$task_id)
    {
        try{
            return $this->finish($finishData,$task_id);
        }catch (\Throwable $throwable){
            $this->onException($throwable);
        }
    }

    abstract protected function run($taskData,$taskId,$fromWorkerId,$flags = null);

    abstract protected function finish($result,$task_id);

    protected function onException(\Throwable $throwable):void
    {
        throw $throwable;
    }
}