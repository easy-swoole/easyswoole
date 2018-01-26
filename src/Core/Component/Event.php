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

    public function hook($event,...$args)
    {
        $call = $this->get($event);
        if($call != null){
            if(is_callable($call)){
                return call_user_func_array($call,$args);
            }else{
                trigger_error("{$event} call is not a callable");
                return false;
            }
        }
    }
}