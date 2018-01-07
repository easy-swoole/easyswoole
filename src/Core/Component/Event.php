<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/29
 * Time: 下午12:25
 */

namespace EasySwoole\Core\Component;


use EasySwoole\Core\AbstractInterface\Singleton;

class Event extends Container
{
    use Singleton;

    public function hook($event,...$args):bool
    {
        $call = $this->get($event);
        if(is_callable($call)){
            call_user_func_array($call,$args);
            return true;
        }else{
            return false;
        }

    }
}