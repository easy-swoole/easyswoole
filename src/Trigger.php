<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/8/14
 * Time: 下午6:17
 */

namespace EasySwoole\EasySwoole;

use EasySwoole\Component\Singleton;
use EasySwoole\Trace\AbstractInterface\TriggerInterface;
use EasySwoole\Trace\Bean\Location;

class Trigger implements TriggerInterface
{
    use Singleton;
    private $trigger;

    public function __construct(TriggerInterface $trigger)
    {
        $this->trigger = $trigger;
    }

    public function error($msg, int $errorCode = E_USER_ERROR, Location $location = null)
    {
        // TODO: Implement error() method.
        $this->trigger->error($msg, $errorCode, $location);
    }

    public function throwable(\Throwable $throwable)
    {
        // TODO: Implement throwable() method.
        $this->trigger->throwable($throwable);
    }
}
