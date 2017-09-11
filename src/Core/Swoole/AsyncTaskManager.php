<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/23
 * Time: 上午12:30
 */

namespace Core\Swoole;



use Core\AbstractInterface\AbstractAsyncTask;
use Core\Component\SuperClosure;

class AsyncTaskManager
{
    private static $instance;
    const TASK_DISPATCHER_TYPE_RANDOM = -1;
    static function getInstance(){
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }
    /**
     *
     *  $data要投递的任务数据，可以为除资源类型之外的任意PHP变量
        $workerId可以制定要给投递给哪个task进程，传入ID即可，范围是0 - (serv->task_worker_num -1)
        调用成功，返回值为整数$task_id，表示此任务的ID。如果有finish回应，onFinish回调中会携带$task_id参数
        调用失败，返回值为false
        未指定目标Task进程，调用task方法会判断Task进程的忙闲状态，底层只会向处于空闲状态的Task进程投递任务
        1.8.6版本增加了第三个参数，可以直接设置onFinish函数，如果任务设置了回调函数，Task返回结果时会直接执行制定的回调函数，
        不再执行Server的onFinish回调
        $task_id是从0-42亿的整数，在当前进程内是唯一的
        task方法不能在task进程/用户自定义进程中调用
     * @param $callable
     * @param int $workerId
     * @param null $finishCallBack
     * @return bool
     */
    function add($callable, $workerId = self::TASK_DISPATCHER_TYPE_RANDOM, $finishCallBack = null){
        if($callable instanceof \Closure){
            try{
                $callable = new SuperClosure($callable);
            }catch (\Exception $exception){
                trigger_error("async task serialize fail ");
                return false;
            }
        }
        return Server::getInstance()->getServer()->task($callable,$workerId,$finishCallBack);
    }
    function addSyncTask($callable,$timeout = 0.5,$workerId = self::TASK_DISPATCHER_TYPE_RANDOM){
        if($callable instanceof \Closure){
            try{
                $callable = new SuperClosure($callable);
            }catch (\Exception $exception){
                trigger_error("async task serialize fail ");
                return false;
            }
        }
        return Server::getInstance()->getServer()->taskwait($callable,$timeout,$workerId);
    }
}