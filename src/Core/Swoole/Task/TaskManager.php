<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/11
 * Time: 下午9:12
 */

namespace EasySwoole\Core\Swoole\Task;
use EasySwoole\Config;
use EasySwoole\Core\Component\SuperClosure;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Swoole\PipeMessage\EventRegister;
use EasySwoole\Core\Swoole\PipeMessage\Message;
use EasySwoole\Core\Swoole\ServerManager;

class TaskManager
{
    public static function async($task,$finishCallback = null,$taskWorkerId = -1)
    {
        if($task instanceof \Closure){
            try{
                $task = new SuperClosure($task);
            }catch (\Throwable $throwable){
                Trigger::throwable($throwable);
                return false;
            }
        }
        return ServerManager::getInstance()->getServer()->task($task,$taskWorkerId,$finishCallback);
    }

    public static function processAsync($task)
    {
        if($task instanceof \Closure){
            try{
                $task = new SuperClosure($task);
            }catch (\Throwable $throwable){
                Trigger::throwable($throwable);
                return false;
            }
        }
        $message = new Message();
        $message->setCommand(EventRegister::TASK);
        $message->setData($task);
        $taskNum = Config::getInstance()->getConf('MAIN_SERVER.SETTING.task_worker_num');
        $workerNum = Config::getInstance()->getConf('MAIN_SERVER.SETTING.worker_num');
        $workerId = mt_rand($workerNum,($workerNum+$taskNum)-1);
        ServerManager::getInstance()->getServer()->sendMessage(\swoole_serialize::pack($message),$workerId);
    }

    public static  function sync($task,$timeout = 0.5,$taskWorkerId = -1)
    {
        if($task instanceof \Closure){
            try{
                $task = new SuperClosure($task);
            }catch (\Throwable $throwable){
                Trigger::throwable($throwable);
                return false;
            }
        }
        return ServerManager::getInstance()->getServer()->taskwait($task,$timeout,$taskWorkerId);
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
                    Trigger::throwable($throwable);
                    return false;
                }
            }
            $temp[] = $task;
            $map[] = $name;
        }
        if(!empty($temp)){
            $ret = ServerManager::getInstance()->getServer()->taskWaitMulti($temp,$timeout);
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