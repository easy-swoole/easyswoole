<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:28
 */

namespace Core\AbstractInterface;


use Core\Swoole\SwooleHttpServer;

abstract class AbstractAsyncTask
{
    protected $resultData;
    protected $taskData;
    /**避免资源类型传递
     * @param null $data
     * @return mixed
     */
    function taskResultData($data = null){
        if($data === null){
            return $this->resultData;
        }else{
            $this->resultData = $data;
        }
    }

    /*
     * 注意   server为task进程的server   但taskId为分配该任务的主worker分配的taskId 为每个主worker进程内独立自增
     */
    abstract function handler(\swoole_http_server $server,$taskId,$fromId);
    /*
     * 注意   server为主worker进程的server   但taskId为分配该任务的主worker分配的taskId 为每个主worker进程内独立自增
     */
    abstract function finishCallBack(\swoole_http_server $server, $task_id,$resultData);
    protected function doFinish($taskResultData = null){
        if($taskResultData !== null){
            $this->taskResultData($taskResultData);
        }
        //为何不用$this传递   避免handler中有释放资源类型被序列化出错
        SwooleHttpServer::getInstance()->getServer()->finish(array(
            "taskClassName"=>static::class,
            "taskResultData"=>$this->resultData,
        ));
    }
}