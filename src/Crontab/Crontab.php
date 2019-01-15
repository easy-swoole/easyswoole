<?php


namespace EasySwoole\EasySwoole\Crontab;

use Cron\CronExpression;
use EasySwoole\Component\Singleton;
use EasySwoole\Component\TableManager;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Crontab\Exception\CronTaskNotExist;
use EasySwoole\EasySwoole\Crontab\Exception\CronTaskRuleInvalid;
use EasySwoole\EasySwoole\ServerManager;
use Swoole\Table;

class Crontab
{
    use Singleton;

    private $tasks = [];
    /*
     * 下划线开头表示不希望用户使用
     */
    public static $__swooleTableName = '__CrontabRuleTable';

    /*
     * 同名任务会被覆盖
     */
    function addTask(string $cronTaskClass): Crontab
    {
        try {
            $ref = new \ReflectionClass($cronTaskClass);
            if ($ref->isSubclassOf(AbstractCronTask::class)) {
                $taskName = $cronTaskClass::getTaskName();
                $taskRule = $cronTaskClass::getRule();
                if (CronExpression::isValidExpression($taskRule)) {
                    $this->tasks[$taskName] = $cronTaskClass;
                } else {
                    throw new CronTaskRuleInvalid($taskName, $taskRule);
                }
                return $this;
            } else {
                throw new \InvalidArgumentException("the cron task class {$cronTaskClass} is invalid");
            }
        } catch (\Throwable $throwable) {
            throw new \InvalidArgumentException("the cron task class {$cronTaskClass} is invalid");
        }
    }

    /**
     * 重新设置某个任务的规则
     * @param string $taskName
     * @param string $taskRule
     * @throws CronTaskNotExist
     * @throws CronTaskRuleInvalid
     */
    function resetTaskRule($taskName, $taskRule)
    {
        $table = TableManager::getInstance()->get(self::$__swooleTableName);
        if ($table->exist($taskName)) {
            if (CronExpression::isValidExpression($taskRule)) {
                $table->set($taskName, ['taskRule' => $taskRule]);
            } else {
                throw new CronTaskRuleInvalid($taskName, $taskRule);
            }
        } else {
            throw new CronTaskNotExist($taskName);
        }
    }

    /**
     * 获取某任务当前的规则
     * 在服务启动完成之前请勿调用！
     * @param string $taskName 任务名称
     * @return string 任务当前规则
     * @throws CronTaskNotExist|\Exception
     */
    function getTaskCurrentRule($taskName)
    {
        $taskInfo = $this->getTableTaskInfo($taskName);
        return $taskInfo['taskRule'];
    }

    /**
     * 获取某任务下次运行的时间
     * 在服务启动完成之前请勿调用！
     * @param string $taskName 任务名称
     * @return integer 任务下次执行的时间戳
     * @throws \Exception
     */
    function getTaskNextRunTime($taskName)
    {
        $taskInfo = $this->getTableTaskInfo($taskName);
        return $taskInfo['taskNextRunTime'];
    }

    /**
     * 获取某任务自启动以来已运行的次数
     * 在服务启动完成之前请勿调用！
     * @param string $taskName 任务名称
     * @return integer 已执行的次数
     * @throws \Exception
     */
    function getTaskRunNumberOfTimes($taskName)
    {
        $taskInfo = $this->getTableTaskInfo($taskName);
        return $taskInfo['taskRunTimes'];
    }

    /**
     * 获取表中存放的Task信息
     * @param string $taskName 任务名称
     * @return array 任务信息
     * @throws CronTaskNotExist|\Exception
     */
    private function getTableTaskInfo($taskName)
    {
        $table = TableManager::getInstance()->get(self::$__swooleTableName);
        if ($table) {
            if ($table->exist($taskName)) {
                return $table->get($taskName);
            } else {
                throw new CronTaskNotExist($taskName);
            }
        } else {
            throw new \Exception('Crontab tasks have not yet started!');
        }
    }

    /*
     * 请用户不要私自调用
     */
    function __run()
    {
        if (!empty($this->tasks)) {
            $server = ServerManager::getInstance()->getSwooleServer();
            $name = Config::getInstance()->getConf('SERVER_NAME');
            $runner = new CronRunner("{$name}.Crontab", $this->tasks);

            // 将当前任务的初始规则全部添加到swTable管理
            TableManager::getInstance()->add(self::$__swooleTableName, [
                'taskRule' => ['type' => Table::TYPE_STRING, 'size' => 35],
                'taskRunTimes' => ['type' => Table::TYPE_INT, 'size' => 4],
                'taskNextRunTime' => ['type' => Table::TYPE_INT, 'size' => 4]
            ], 1024);

            $table = TableManager::getInstance()->get(self::$__swooleTableName);

            // 由于添加时已经确认过任务均是AbstractCronTask的子类 这里不再去确认
            foreach ($this->tasks as $cronTaskName => $cronTaskClass) {
                $taskRule = $cronTaskClass::getRule();
                $nextTime = CronExpression::factory($taskRule)->getNextRunDate()->getTimestamp();
                $table->set($cronTaskName, ['taskRule' => $taskRule, 'taskRunTimes' => 0, 'taskNextRunTime' => $nextTime]);
            }

            $server->addProcess($runner->getProcess());
        }
    }
}