<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Component\Event;
use EasySwoole\EasySwoole\Bridge\CommandHandel\Config;
use EasySwoole\EasySwoole\Bridge\CommandHandel\Crontab;
use EasySwoole\EasySwoole\Bridge\CommandHandel\Server;
use EasySwoole\EasySwoole\Bridge\CommandHandel\Task;
use EasySwoole\EasySwoole\Bridge\CommandHandel\Process;

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
        Server::initCommand($this);
        Crontab::initCommand($this);
        Process::initCommand($this);
        Task::initCommand($this);
        Config::initCommand($this);
    }
}
