<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/28
 * Time: 下午8:52
 */

namespace EasySwoole\Core\Component\Crontab;


use Cron\CronExpression;
use EasySwoole\Core\Component\Trigger;
use EasySwoole\Core\Swoole\Process\AbstractProcess;
use EasySwoole\Core\Swoole\Task\TaskManager;
use Swoole\Process;

class Runner extends AbstractProcess
{
    protected $allTask = [];
    public function run(Process $process)
    {
        // TODO: Implement run() method.
        $this->allTask = $this->getArgs();
        //crontab最小执行单位是1min,因此29s检测一次,以半分钟为周期
        $checkTask = function (){
            $allToDo = [];
            $current = time();
            foreach ($this->allTask as $task){
                //若相差小于30s  则投递执行
                try {
                    $rule = $task['rule'];
                    $time = CronExpression::factory($rule)->getNextRunDate()->getTimestamp();
                    //过期任务不执行
                    if ($time > $current && ($time - $current <= 30)) {
                        $allToDo[] = ['task' => $task['task'], 'time' => $time - $current];
                    }
                } catch (\Throwable $throwable) {
                    Trigger::throwable($throwable);
                }
            }
            if(!empty($allToDo)){
                // 检测时已得知距离目标执行时间的秒数
                foreach ($allToDo as $task){
                    $this->delay($task['time']*1000,function ()use($task){
                        TaskManager::processAsync($task['task']);
                    });
                }
            }
        };
        // 因为定时器不是立马调用 启动时立即检查一次
        $checkTask();
        $this->addTick(30000,$checkTask);
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