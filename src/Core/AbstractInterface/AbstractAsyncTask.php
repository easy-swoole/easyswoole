<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:28
 */

namespace Core\AbstractInterface;


use Core\Swoole\Server;

abstract class AbstractAsyncTask
{
    private $dataForFinishCallBack;
    private $dataForTask;
    function __construct($dataForTask = null)
    {
        $this->dataForTask = $dataForTask;
    }

    /**
     * @return mixed
     */
    public function getDataForTask()
    {
        return $this->dataForTask;
    }

    /**
     * @param mixed $dataForTask
     */
    public function setDataForTask($dataForTask)
    {
        $this->dataForTask = $dataForTask;
    }

    /**
     * @return mixed
     */
    public function getDataForFinishCallBack()
    {
        return $this->dataForFinishCallBack;
    }
    /*
     * 注意   server为task进程的server   但taskId为分配该任务的主worker分配的taskId 为每个主worker进程内独立自增
     */
    abstract function handler(\swoole_server $server,$taskId,$fromId);
    /*
     * 注意   server为主worker进程的server   但taskId为分配该任务的主worker分配的taskId 为每个主worker进程内独立自增
     */
    abstract function finishCallBack(\swoole_server $server, $task_id,$resultData);
    protected function finish($dataForFinishCallBack = null){
        if($dataForFinishCallBack !== null){
            $this->dataForFinishCallBack = $dataForFinishCallBack;
        }
        //为何不用$this传递   避免handler中有释放资源类型被序列化出错
        Server::getInstance()->getServer()->finish($this);
    }
}