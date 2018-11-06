<?php


namespace EasySwoole\EasySwoole\Crontab;

use Cron\CronExpression;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\ServerManager;

class Crontab
{
    use Singleton;

    private $tasks = [];

    /*
     * 同名任务会被覆盖
     */
    function addTask(string $cronTaskClass): Crontab
    {
        try{
            $ref = new \ReflectionClass($cronTaskClass);
            if($ref->isSubclassOf(AbstractCronTask::class)){
                $rule = $cronTaskClass::getRule();
                if(CronExpression::isValidExpression($rule)){
                    $this->tasks[$cronTaskClass::getTaskName()] = $cronTaskClass;
                }else{
                    throw new \InvalidArgumentException("the cron expression {$rule} is invalid");
                }
                return $this;
            }else{
                throw new \InvalidArgumentException("the cron task class {$cronTaskClass} is invalid");
            }
        }catch (\Throwable $throwable){
            throw new \InvalidArgumentException("the cron task class {$cronTaskClass} is invalid");
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
            $server->addProcess($runner->getProcess());
        }
    }
}