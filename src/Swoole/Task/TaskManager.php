<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/29
 * Time: 上午11:36
 */

namespace EasySwoole\EasySwoole\Swoole\Task;


use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Swoole\PipeMessage\Message;
use EasySwoole\EasySwoole\Trigger;

class TaskManager
{
    public static function async($task,$finishCallback = null,$taskWorkerId = -1)
    {
        if($task instanceof \Closure){
            try{
                $task = new SuperClosure($task);
            }catch (\Throwable $throwable){
                Trigger::getInstance()->throwable($throwable);
                return false;
            }
        }
        return ServerManager::getInstance()->getSwooleServer()->task($task,$taskWorkerId,$finishCallback);
    }

    public static function processAsync($task)
    {
        $conf = ServerManager::getInstance()->getSwooleServer()->setting;
        if (!isset($conf['task_worker_num'])) {
            return false;
        }
        $taskNum = $conf['task_worker_num'];
        $workerNum = $conf['worker_num'];
        
        // 随机取进程，避免取到同一进程。
        // fixed：[Swoole\Server::sendMessage(): can't send message to self.]
        // tips:开启的task进程数过少时，若自定义进程执行嵌套的异步task会出现随机到同一进程
        mt_srand();
        $workerId = null;
        for ($i = 0; $i < $taskNum; $i++) {
            $workerId = mt_rand($workerNum, ($workerNum+$taskNum)-1);
            if ($workerId !== ServerManager::getInstance()->getSwooleServer()->worker_id) {
                break;
            }
        }
        if (!$workerId || $workerId === ServerManager::getInstance()->getSwooleServer()->worker_id) {
            // 无空闲task进程，自动切为同步阻塞执行
            $task->run($task->getData(), -1, ServerManager::getInstance()->getSwooleServer()->worker_id);
        } else {
            if ($task instanceof \Closure) {
                try {
                    $task = new SuperClosure($task);
                } catch (\Throwable $throwable) {
                    Trigger::getInstance()->throwable($throwable);
                    return false;
                }
            }
            $message = new Message();
            $message->setCommand('TASK');
            $message->setData($task);
            ServerManager::getInstance()->getSwooleServer()->sendMessage(serialize($message), $workerId);
        }
        return true;
    }

    public static  function sync($task,$timeout = 0.5,$taskWorkerId = -1)
    {
        if($task instanceof \Closure){
            try{
                $task = new SuperClosure($task);
            }catch (\Throwable $throwable){
                Trigger::getInstance()->throwable($throwable);
                return false;
            }
        }
        return ServerManager::getInstance()->getSwooleServer()->taskwait($task,$timeout,$taskWorkerId);
    }

    public static  function barrier(array $taskList,$timeout = 0.5)
    {
        $temp =[];
        $map = [];
        $result = [];
        foreach ($taskList as $name => $task){
            if($task instanceof \Closure){
                try{
                    $task = new SuperClosure($task);
                }catch (\Throwable $throwable){
                    Trigger::getInstance()->throwable($throwable);
                    return false;
                }
            }
            $temp[] = $task;
            $map[] = $name;
        }
        if(!empty($temp)){
            $ret = ServerManager::getInstance()->getSwooleServer()->taskWaitMulti($temp,$timeout);
            if(!empty($ret)){
                //极端情况下  所有任务都超时
                foreach ($ret as $index => $subRet){
                    $result[$map[$index]] = $subRet;
                }
            }
        }
        return $result;
    }
}
