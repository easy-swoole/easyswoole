<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/4/30
 * Time: 下午4:11
 */

namespace EasySwoole\Core\Component\Cluster\Callback;


use EasySwoole\Core\Component\Container;
use EasySwoole\Core\Component\Invoker;
use EasySwoole\Core\Component\Trigger;

class BaseContainer extends Container
{
    function call(...$arg){
        $calls = $this->all();
        foreach ($calls as $call){
            try{
                Invoker::callUserFunc($call,...$arg);
            }catch (\Throwable $throwable){
                Trigger::throwable($throwable);
            }
        }
    }
}