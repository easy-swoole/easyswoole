<?php


namespace EasySwoole\EasySwoole\Task;


use EasySwoole\Component\Singleton;
use EasySwoole\Task\Task;
use EasySwoole\Task\Config as TaskConfig;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Trigger;

class TaskManager extends Task
{
    use Singleton;

    public function __construct(TaskConfig|null $config = null)
    {
        parent::__construct($config);
        $config = $this->getConfig();
        $config->restore(Config::getInstance()->getConf('MAIN_SERVER.TASK') ?? []);
        $config->setTempDir(EASYSWOOLE_TEMP_DIR);
        $config->setServerName(Config::getInstance()->getConf('SERVER_NAME') . '.' . Core::getInstance()->runMode());
        $config->setOnException(static function (\Throwable $throwable): void {
            Trigger::getInstance()->throwable($throwable);
        });
    }
}