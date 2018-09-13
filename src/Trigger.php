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

    function __construct(TriggerInterface $trigger = null)
    {
        if($trigger == null){
            $trigger = new TriggerHandler(Logger::getInstance());
        }
        parent::__construct($trigger);
    }
}