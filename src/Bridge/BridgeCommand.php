<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Component\Event;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;

class BridgeCommand extends Event
{
    const PROCESS_INFO = 101;
    const SERVER_STATUS_INFO = 102;
    const TASK_INFO = 103;
    const CRON_INFO = 201;
    const CRON_STOP = 202;
    const CRON_RESUME = 203;
    const CONFIG_INFO = 301;
    const CONFIG_SET = 302;


    function __construct(array $allowKeys = null)
    {
        parent::__construct($allowKeys);
        $this->set(self::CONFIG_INFO,function (Package $package){
            return self::configInfo($package);
        });
    }


    private static function configInfo(Package $package)
    {
        $data = $package->getArgs();
        if (empty($data['key'])){
            $configArray = Config::getInstance()->toArray();
            $configArray['mode'] = Core::getInstance()->isDev() ? 'develop' : 'produce';
            return $configArray;
        }
        $configArray = Config::getInstance()->getConf($data['key']);
        return $configArray;
    }
}
