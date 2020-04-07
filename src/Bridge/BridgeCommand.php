<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Component\Event;
use EasySwoole\Component\MultiContainer;

class BridgeCommand extends Event
{
    const PROCESS_INFO = 101;
    const SERVER_STATUS_INFO = 102;
    const TASK_INFO = 103;
    const CRON_INFO = 201;
    const CRON_STOP = 202;
    const CRON_RESUME = 203;
}
