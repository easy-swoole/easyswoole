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
        $workerNum = $conf['worker_num'];
        if(!isset($conf['task_worker_num'])){
            return false;
        }
        $taskNum = $conf['task_worker_num'];
        $closure = false;
        if($task instanceof \Closure){
            try{
                $task = new SuperClosure($task);
                $closure = true;
            }catch (\Throwable $throwable){
                Trigger::getInstance()->throwable($throwable);
                return false;
            }
        }
        $message = new Message();
        $message->setCommand('TASK');
        $message->setData($task);
        mt_srand();
        //闭包无法再onPipeMessage中再次被序列化，因此直接投递给task进程直接执行。
        if($closure){
            $workerId = mt_rand($workerNum,($workerNum+$taskNum)-1);
        }else{
            $workerId = mt_rand(0,$workerNum -1);
        }
        ServerManager::getInstance()->getSwooleServer()->sendMessage(serialize($message),$workerId);
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