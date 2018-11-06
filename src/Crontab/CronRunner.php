<?php
/**
 * Created by PhpStorm.
 * User: eValor
 * Date: 2018/11/6
 * Time: 上午12:13
 */

namespace EasySwoole\EasySwoole\Crontab;

use Cron\CronExpression;
use EasySwoole\EasySwoole\Swoole\Process\AbstractProcess;
use EasySwoole\EasySwoole\Swoole\Task\TaskManager;
use EasySwoole\EasySwoole\Swoole\Time\Timer;
use Swoole\Process;

class CronRunner extends AbstractProcess
{
    protected $tasks;

    public function run(Process $process)
    {
        $this->tasks = $this->getArg('tasks');
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
        foreach ($this->tasks as $taskName => $taskConst) {
            $cronRule = $taskConst['rule'];
            $cronClosure = $taskConst['task'];

            $nextRunTime = CronExpression::factory($cronRule)->getNextRunDate();
            $distanceTime = $nextRunTime->getTimestamp() - time();
            if ($distanceTime < 30) {
                Timer::delay($distanceTime * 1000, function () use ($cronClosure) {
                    TaskManager::processAsync($cronClosure);
                });
            }
        }
    }
}