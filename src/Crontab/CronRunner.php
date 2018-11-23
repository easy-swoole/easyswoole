<?php
/**
 * Created by PhpStorm.
 * User: eValor
 * Date: 2018/11/6
 * Time: 上午12:13
 */

namespace EasySwoole\EasySwoole\Crontab;

use Cron\CronExpression;
use EasySwoole\EasySwoole\Swoole\Memory\TableManager;
use EasySwoole\EasySwoole\Swoole\Process\AbstractProcess;
use EasySwoole\EasySwoole\Swoole\Task\TaskManager;
use EasySwoole\EasySwoole\Swoole\Time\Timer;
use Swoole\Process;

class CronRunner extends AbstractProcess
{
    protected $tasks;

    public function run(Process $process)
    {
        $this->tasks = $this->getArgs();
        $this->cronProcess();
        Timer::loop(29 * 1000, function () {
            $this->cronProcess();
        });
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }

    public function onReceive(string $str)
    {
        // TODO: Implement onReceive() method.
    }

    private function cronProcess()
    {
        $table = TableManager::getInstance()->get('CrontabRuleTable');
        foreach ($table as $taskName => $task) {
            $taskRule = $task['taskRule'];
            $nextRunTime = CronExpression::factory($task['taskRule'])->getNextRunDate();
            $distanceTime = $nextRunTime->getTimestamp() - time();
            if ($distanceTime < 30) {
                Timer::delay($distanceTime * 1000, function () use ($taskName, $taskRule) {
                    $nextRunTime = CronExpression::factory($taskRule)->getNextRunDate();
                    $table = TableManager::getInstance()->get('CrontabRuleTable');
                    $table->incr($taskName, 'taskRunTimes', 1);
                    $table->set($taskName, ['taskNextRunTime' => $nextRunTime->getTimestamp()]);
                    TaskManager::processAsync($this->tasks[$taskName]);
                });
            }
        }
    }
}