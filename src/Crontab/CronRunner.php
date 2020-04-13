<?php
/**
 * Created by PhpStorm.
 * User: eValor
 * Date: 2018/11/6
 * Time: 上午12:13
 */

namespace EasySwoole\EasySwoole\Crontab;

use Cron\CronExpression;
use EasySwoole\Component\Timer;
use EasySwoole\Component\Process\AbstractProcess;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\EasySwoole\Trigger;
use Swoole\Table;

class CronRunner extends AbstractProcess
{
    protected $tasks;

    public function run($arg)
    {
        $tasks = $arg;
        /** @var Table $table */
        $table = Crontab::getInstance()->infoTable();
        /*
         * 先清空一遍规则
         */
        foreach ($table as $key => $value) {
            $table->del($key);
        }
        //这部分的解析，迁移到Crontab.php做
        foreach ($tasks as $taskName => $cronTaskClass) {
            /**
             * @var $cronTaskClass AbstractCronTask
             */
            $taskName = $cronTaskClass::getTaskName();
            $taskRule = $cronTaskClass::getRule();
            $nextTime = CronExpression::factory($taskRule)->getNextRunDate()->getTimestamp();
            $table->set($taskName, ['taskRule' => $taskRule, 'taskRunTimes' => 0, 'taskNextRunTime' => $nextTime, 'isStop' => 0]);
            $this->tasks[$taskName] = $cronTaskClass;
        }
        $this->cronProcess();
        Timer::getInstance()->loop(29 * 1000, function () {
            $this->cronProcess();
        });
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }

    private function cronProcess()
    {
        $table = Crontab::getInstance()->infoTable();
        foreach ($table as $taskName => $task) {
            if ($task['isStop']) {
                continue;
            }
            $taskRule = $task['taskRule'];
            $nextRunTime = CronExpression::factory($task['taskRule'])->getNextRunDate();
            $distanceTime = $nextRunTime->getTimestamp() - time();
            if ($distanceTime < 30) {
                Timer::getInstance()->after($distanceTime * 1000, function () use ($taskName, $taskRule) {
                    $table = Crontab::getInstance()->infoTable();
                    $nextRunTime = CronExpression::factory($taskRule)->getNextRunDate();
                    $table->incr($taskName, 'taskRunTimes', 1);
                    $table->set($taskName, ['taskNextRunTime' => $nextRunTime->getTimestamp()]);
                    TaskManager::getInstance()->async($this->tasks[$taskName]);
                });
            }
        }
    }
}
