<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/29
 * Time: 上午11:36
 */

namespace EasySwoole\EasySwoole\Swoole\Task;


use EasySwoole\EasySwoole\ServerManager;
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
        return self::async($task);
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

    public static function barrier(array $taskList,$timeout = 0.5):array
    {
        return self::taskCo($taskList,$timeout);
    }

    public static function taskCo(array $taskList,$timeout = 0.5):array
    {
        $taskMap = [];
        $finalTask = [];
        foreach ($taskList as $key => $item){
            if($item instanceof \Closure){
                try{
                    $temp = new SuperClosure($item);
                    $taskList[$key] = $temp;
                }catch (\Throwable $throwable){
                    unset($taskList[$key]);
                    Trigger::getInstance()->throwable($throwable);
                }
            }
            if(isset($taskList[$key])){
                $finalTask[] = $taskList[$key];
                $taskMap[count($finalTask) - 1] = $key;
            }
        }
        $result = [];
        $ret = ServerManager::getInstance()->getSwooleServer()->taskCo($taskList,$timeout);
        if(is_array($ret)){
            foreach ($ret as $index => $temp){
                $result[$taskMap[$index]] = $temp;
            }
        }
        return $result;
    }
}