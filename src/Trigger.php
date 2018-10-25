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
use EasySwoole\Trace\DefaultHandler\TriggerHandler;

class Trigger extends \EasySwoole\Trace\Trigger
{
    use Singleton;

    /*
     * 自带的TriggerHandler用的是自带的Logger,里面开启了控制台socket 推送。如果你重写了TriggerHandler，请自己复制Logger里面的推送逻辑
     */
    function __construct(TriggerInterface $trigger = null)
    {
        if($trigger == null){
            $trigger = new TriggerHandler(Logger::getInstance());
        }
        parent::__construct($trigger);
    }
}