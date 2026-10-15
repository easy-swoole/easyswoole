<?php


namespace EasySwoole\EasySwoole\Crontab;


use EasySwoole\Component\Singleton;
use EasySwoole\Crontab\Config as CrontabConfig;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Trigger;

class Crontab extends \EasySwoole\Crontab\Crontab
{
    use Singleton;

    public function __construct(CrontabConfig|null $config = null)
    {
        parent::__construct($config);
        $config = $this->getConfig();
        $config->setTempDir(EASYSWOOLE_TEMP_DIR);
        $config->setServerName(Config::getInstance()->getConf('SERVER_NAME') . '.' . Core::getInstance()->runMode());
        $config->setOnException(static function (\Throwable $throwable): void {
            Trigger::getInstance()->throwable($throwable);
        });
    }
}