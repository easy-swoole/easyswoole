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
        foreach ($table as $key => $value){
            $table->del($key);
        }

        foreach ($tasks as $cronTaskClass) {
            try {
                $ref = new \ReflectionClass($cronTaskClass);
                if ($ref->isSubclassOf(AbstractCronTask::class)) {
                    $taskName = $cronTaskClass::getTaskName();
                    $taskRule = $cronTaskClass::getRule();
                    if (CronExpression::isValidExpression($taskRule)) {
                        $this->tasks[$taskName] = $cronTaskClass;
                        $nextTime = CronExpression::factory($taskRule)->getNextRunDate()->getTimestamp();
                        $table->set($taskName, ['taskRule' => $taskRule, 'taskRunTimes' => 0, 'taskNextRunTime' => $nextTime,'isStop'=>0]);
                    } else {
                        Trigger::getInstance()->error("{$cronTaskClass} not a valid cron task");
                    }
                } else {
                    throw new \InvalidArgumentException("the cron task class {$cronTaskClass} is invalid");
                }
            } catch (\Throwable $throwable) {
                Trigger::getInstance()->throwable($throwable);
            }
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
            if($task['isStop']){
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