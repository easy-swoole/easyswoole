<?php
/**
 * Created by PhpStorm.
 * User: eValor
 * Date: 2018/11/6
 * Time: 上午12:13
 */

namespace EasySwoole\EasySwoole\Crontab;

use Cron\CronExpression;
use EasySwoole\Component\TableManager;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Swoole\Task\TaskManager;
use EasySwoole\Component\Process\AbstractProcess;

class CronRunner extends AbstractProcess
{
    protected $tasks;

    public function run($arg)
    {
        $this->tasks = $arg;
        $this->cronProcess();
        Timer::getInstance()->loop(29 * 1000, function () {
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
        $table = TableManager::getInstance()->get(Crontab::$__swooleTableName);
        foreach ($table as $taskName => $task) {
            $taskRule = $task['taskRule'];
            $nextRunTime = CronExpression::factory($task['taskRule'])->getNextRunDate();
            $distanceTime = $nextRunTime->getTimestamp() - time();
            if ($distanceTime < 30) {
                Timer::getInstance()->after($distanceTime * 1000, function () use ($taskName, $taskRule) {
                    $nextRunTime = CronExpression::factory($taskRule)->getNextRunDate();
                    $table = TableManager::getInstance()->get(Crontab::$__swooleTableName);
                    $table->incr($taskName, 'taskRunTimes', 1);
                    $table->set($taskName, ['taskNextRunTime' => $nextRunTime->getTimestamp()]);
                    TaskManager::processAsync($this->tasks[$taskName]);
                });
            }
        }
    }
}