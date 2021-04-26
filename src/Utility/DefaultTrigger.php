<?php


namespace EasySwoole\EasySwoole\Utility;


use EasySwoole\Trigger\Location;
use EasySwoole\Trigger\TriggerInterface;

class DefaultTrigger implements TriggerInterface
{

    public function error($msg, int $errorCode = E_USER_ERROR, Location $location = null)
    {
        // TODO: Implement error() method.
    }

    public function throwable(\Throwable $throwable)
    {
        // TODO: Implement throwable() method.
    }
}