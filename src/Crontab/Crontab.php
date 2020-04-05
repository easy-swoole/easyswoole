<?php


namespace EasySwoole\EasySwoole\Crontab;

use Cron\CronExpression;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Crontab\Exception\CronTaskNotExist;
use EasySwoole\EasySwoole\Crontab\Exception\CronTaskRuleInvalid;
use EasySwoole\EasySwoole\ServerManager;
use Swoole\Table;
use EasySwoole\Component\Process\Config as ProcessConfig;

class Crontab
{
    use Singleton;

    private $table;
    private $tasks = [];

    function __construct()
    {
        $this->table = new Table(1024);
        $this->table->column('taskRule',Table::TYPE_STRING,35);
        $this->table->column('taskRunTimes',Table::TYPE_INT,8);
        $this->table->column('taskNextRunTime',Table::TYPE_INT,8);
        $this->table->column('isStop',Table::TYPE_INT,1);
        $this->table->create();
    }

    /*
     * 一个class只会被注册一次
     */
    function addTask(string $cronTaskClass): Crontab
    {
        //任务解析，存成   taskName=> taskInfo格式
        $ref = new \ReflectionClass($cronTaskClass);
        $this->tasks[$cronTaskClass] = $cronTaskClass;
        return $this;

    }


    function rightNow(string $taskName)
    {
        //立即在同进程执行一次，不再投递到task
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
        if ($this->table->exist($taskName)) {
            if (CronExpression::isValidExpression($taskRule)) {
                $this->table->set($taskName, ['taskRule' => $taskRule]);
            } else {
                throw new CronTaskRuleInvalid($taskName, $taskRule);
            }
        } else {
            throw new CronTaskNotExist($taskName);
        }
    }

    /**
     * 获取表中存放的Task信息
     * @param string $taskName 任务名称
     * @return array 任务信息
     * @throws CronTaskNotExist|\Exception
     */
    private function getTaskInfo($taskName)
    {
        if ($this->table->exist($taskName)) {
            return $this->table->get($taskName);
        } else {
            throw new CronTaskNotExist($taskName);
        }
    }

    function infoTable():Table
    {
        return $this->table;
    }


    /*
     * 请用户不要私自调用
     */
    function __run()
    {
        if (!empty($this->tasks)) {
            $server = ServerManager::getInstance()->getSwooleServer();
            $name = Config::getInstance()->getConf('SERVER_NAME');
            $config = new ProcessConfig();
            $config->setArg($this->tasks);
            $config->setProcessName("{$name}.Crontab");
            $config->setProcessGroup("EasySwoole.Crontab");
            $runner = new CronRunner($config);
            $server->addProcess($runner->getProcess());
        }
    }
}