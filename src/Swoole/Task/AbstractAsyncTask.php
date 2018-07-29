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
    private $result = null;
    final public function __construct($data = null)
    {
        $this->data = $data;
    }

    /**
     * @return null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    public function setResult($data):void
    {
        $this->result = $data;
    }

    abstract function run($taskData,$taskId,$fromWorkerId);

    abstract function finish($result,$task_id);

    public function onException(\Throwable $throwable):void
    {
        throw $throwable;
    }
}