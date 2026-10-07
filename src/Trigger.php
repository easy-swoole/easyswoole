<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/8/14
 * Time: 下午6:17
 */

namespace EasySwoole\EasySwoole;


use EasySwoole\Component\Event;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\AbstractInterface\Log\TriggerInterface;
use EasySwoole\EasySwoole\AbstractInterface\Log\TriggerLocation;
use EasySwoole\EasySwoole\Utility\DefaultTrigger;

class Trigger
{
    use Singleton;

    private TriggerInterface $trigger;

    private mixed $onError = null;

    private mixed $onThrowable = null;


    function __construct(TriggerInterface $trigger)
    {
        $this->trigger = $trigger;
    }

    function setOnError(callable $callback): void
    {
        $this->onError = $callback;
    }

    function setOnThrowable(callable $callback): void
    {
        $this->onThrowable = $callback;
    }

    public function error($msg,int $errorCode = E_USER_ERROR,TriggerLocation|null $location = null)
    {
        if($location == null){
            $location = $this->getLocation();
        }
        if($this->onError){
            $ret = call_user_func($this->onError,$msg,$errorCode,$location);
            if($ret === false){
                return;
            }
        }
        $this->trigger->error($msg,$errorCode,$location);
    }

    public function throwable(\Throwable $throwable)
    {
        if($this->onThrowable){
            $ret = call_user_func($this->onThrowable,$throwable);
            if($ret === false){
                return;
            }
        }
        $this->trigger->throwable($throwable);
    }

    private function getLocation():TriggerLocation
    {
        $location = new TriggerLocation();
        $debugTrace = debug_backtrace();
        array_shift($debugTrace);
        $caller = array_shift($debugTrace);
        $location->setLine($caller['line']);
        $location->setFile($caller['file']);
        return $location;
    }
}