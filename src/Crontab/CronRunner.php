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
use Swoole\Table;

class CronRunner extends AbstractProcess
{
    protected $tasks;

    protected $timerIds;

    public function run($arg)
    {
        $tasks = $arg;
        /** @var Table $table */
        $table = Crontab::getInstance()->infoTable();
        //先清空一遍规则,禁止循环的时候删除key
        $keys = [];
        foreach ($table as $key => $value) {
            $keys[] = $key;
        }
        foreach ($keys as $key) {
            $table->del($key);
        }
        //这部分的解析，迁移到Crontab.php做
        foreach ($tasks as $taskName => $cronTaskClass) {
            /** @ @var $cronTaskClass AbstractCronTask */
            $taskName = $cronTaskClass::getTaskName();
            $taskRule = $cronTaskClass::getRule();
            $nextTime = CronExpression::factory($taskRule)->getNextRunDate()->getTimestamp();
            $table->set($taskName, ['taskRule' => $taskRule, 'taskRunTimes' => 0, 'taskNextRunTime' => $nextTime, 'taskCurrentRunTime' => 0, 'isStop' => 0]);
            $this->tasks[$taskName] = $cronTaskClass;
        }
        $this->cronProcess();
        //60无法被8整除。
        Timer::getInstance()->loop(8 * 1000, function () {
            $this->cronProcess();
        });
    }

    private function cronProcess()
    {
        $table = Crontab::getInstance()->infoTable();
        foreach ($table as $taskName => $task) {
            if ($task['isStop']) {
                continue;
            }
            $nextRunTime = CronExpression::factory($task['taskRule'])->getNextRunDate()->getTimestamp();
            if ($task['taskNextRunTime'] != $nextRunTime) {
                $table->set($taskName, ['taskNextRunTime' => $nextRunTime]);
            }
            if (isset($this->timerIds[$taskName])) {
                //本轮已经创建过任务
                continue;
            }
            $distanceTime = $nextRunTime - time();
            $timerId = Timer::getInstance()->after($distanceTime * 1000, function () use ($taskName) {
                $table = Crontab::getInstance()->infoTable();
                $table->incr($taskName, 'taskRunTimes', 1);
                $table->set($taskName, ['taskCurrentRunTime' => time()]);
                unset($this->timerIds[$taskName]);
                TaskManager::getInstance()->async($this->tasks[$taskName]);
            });
            if ($timerId) {
                $this->timerIds[$taskName] = $timerId;
            }
        }
    }
}
