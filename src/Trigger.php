<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/8/14
 * Time: 下午6:17
 */

namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Singleton;
use EasySwoole\Trigger\Location;
use EasySwoole\Trigger\TriggerInterface;

class Trigger implements TriggerInterface
{
    use Singleton;

    private $trigger;

    private $onError;
    private $onException;

    function __construct(TriggerInterface $trigger)
    {
        $this->trigger = $trigger;
    }

    public function error($msg,int $errorCode = E_USER_ERROR,Location $location = null)
    {
        // TODO: Implement error() method.
        if($location == null){
            $location = $this->getLocation();
        }
        $this->trigger->error($msg,$errorCode,$location);
        if($this->onError){
            call_user_func($this->onError,$msg,$errorCode,$location);
        }
    }

    public function throwable(\Throwable $throwable)
    {
        // TODO: Implement throwable() method.
        $this->trigger->throwable($throwable);
        if($this->onException){
            call_user_func($this->onException,$throwable);
        }
    }

    public function onError(callable $call)
    {
        $this->onError = $call;
    }

    public function onException(callable $call)
    {
        $this->onException = $call;
    }

    private function getLocation():Location
    {
        $location = new Location();
        $debugTrace = debug_backtrace();
        array_shift($debugTrace);
        $caller = array_shift($debugTrace);
        $location->setLine($caller['line']);
        $location->setFile($caller['file']);
        return $location;
    }
}