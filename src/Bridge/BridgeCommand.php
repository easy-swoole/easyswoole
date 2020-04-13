<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Component\Event;
use EasySwoole\EasySwoole\Bridge\DefaultCommand\Config;
use EasySwoole\EasySwoole\Bridge\DefaultCommand\Crontab;
use EasySwoole\EasySwoole\Bridge\DefaultCommand\Server;
use EasySwoole\EasySwoole\Bridge\DefaultCommand\Task;
use EasySwoole\EasySwoole\Bridge\DefaultCommand\Process;

class BridgeCommand extends Event
{
    const PROCESS_INFO = 101;
    const SERVER_STATUS_INFO = 102;
    const TASK_INFO = 103;
    const CRON_INFO = 201;
    const CRON_STOP = 202;
    const CRON_RESUME = 203;
    const CRON_RUN = 204;
    const CONFIG_INFO = 301;
    const CONFIG_SET = 302;


    function __construct(array $allowKeys = null)
    {
        parent::__construct($allowKeys);
        Server::initCommand($this);
        Crontab::initCommand($this);
        Process::initCommand($this);
        Task::initCommand($this);
        Config::initCommand($this);
    }
}
