<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/7
 * Time: 下午10:35
 */

namespace EasySwoole\Core\Swoole;


use EasySwoole\Core\Component\SuperClosure;

class TaskManager
{
    static public function async($task,$finishCallback = null,$taskWorkerId = -1)
    {
        if($task instanceof \Closure){
            $task = new SuperClosure($task);
        }
        Server::getInstance()->getServer()->task($task,$taskWorkerId,$finishCallback);
    }

    static public function sync($task,$timeout = 0.5,$taskWorkerId = -1){
        if($task instanceof \Closure){
            $task = new SuperClosure($task);
        }
        return Server::getInstance()->getServer()->taskwait($task,$timeout,$taskWorkerId);
    }
}