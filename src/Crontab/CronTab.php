<?php
/**
 * Created by PhpStorm.
 * User: eValor
 * Date: 2018/11/6
 * Time: 上午12:15
 */

namespace EasySwoole\EasySwoole\Crontab;

use Cron\CronExpression;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\ServerManager;

class CronTab
{
    use Singleton;

    private $tasks;

    /**
     * 新增一条需要执行的任务
     * @param string $taskName 新增的任务名称
     * @param string $cronRule 新增的任务规则
     * @param \Closure $taskClosure 需要执行的任务内容
     * @return CronTab
     * @author: eValor < master@evalor.cn >
     */
    function addRule(string $taskName, string $cronRule, \Closure $taskClosure): CronTab
    {
        if (CronExpression::isValidExpression($cronRule)) {
            $this->tasks[$taskName] = [
                'rule' => $cronRule,
                'task' => $taskClosure
            ];
            return $this;
        } else {
            throw new \InvalidArgumentException("the cron expression '{$cronRule}' are invalid");
        }
    }

    /**
     * 启动计划任务
     * @author: eValor < master@evalor.cn >
     */
    function run()
    {
        if (!empty($this->tasks)) {
            $server = ServerManager::getInstance()->getSwooleServer();
            $runner = new CronRunner('CronTaskRunner', [ 'tasks' => $this->tasks ]);
            $server->addProcess($runner->getProcess());
        } else {
            throw new \InvalidArgumentException("the function 'run' cannot be called when the cron task is empty");
        }
    }
}