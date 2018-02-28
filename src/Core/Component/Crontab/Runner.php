<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/28
 * Time: 下午8:52
 */

namespace EasySwoole\Core\Component\Crontab;


use EasySwoole\Core\Swoole\Process\AbstractProcess;
use EasySwoole\Core\Swoole\Task\TaskManager;
use Swoole\Process;

class Runner extends AbstractProcess
{

    public function run(Process $process)
    {
        // TODO: Implement run() method.
        $allTask = $this->getArgs();
        $this->addTick(1000,function ()use($allTask){
            foreach ($allTask as $task){
                //解析每个规则，判断该任务的最近执行时间，如果需要执行则投递到异步任务执行并标记已经执行。
                $rule = $task['rule'];
                $call = $task['task'];
                TaskManager::processAsync($call);
            }
        });
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }

    public function onReceive(string $str, ...$args)
    {
        // TODO: Implement onReceive() method.
    }
}