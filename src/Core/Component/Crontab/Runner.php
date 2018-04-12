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
        $this->addTick(30000,function (){
            $allToDo = [];
            $current = time();
            foreach ($this->allTask as $task){
                //若相差小于30s  则投递执行
                try{
                    $rule = $task['rule'];
                    $time = CronExpression::factory($rule)->getNextRunDate()->getTimestamp();
                    //过期任务不执行
                    if($time > $current && ($time - $current <= 30)){
                        $allToDo[] =  $task['task'];

                    }
                }catch (\Throwable $throwable){
                    Trigger::throwable($throwable);
                }
            }
            if(!empty($allToDo)){
                $this->delay(30000,function ()use($allToDo){
                    foreach ($allToDo as $task){
                        TaskManager::processAsync($task);
                    }
                });
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