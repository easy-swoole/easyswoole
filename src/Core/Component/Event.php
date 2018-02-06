<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/29
 * Time: 下午12:25
 */

namespace EasySwoole\Core\Component;


use EasySwoole\Core\AbstractInterface\Singleton;

class Event extends MultiContainer
{
    use Singleton;

    function add($key, $item)
    {
        if(is_callable($item)){
            parent::add($key, $item);
        }
    }

    function set($key, $item)
    {
        if(is_callable($item)){
            parent::set($key, $item);
        }
    }

    public function hook($event,...$args)
    {
        $calls = $this->get($event);
        if(is_array($calls)){
            foreach ($calls as $call){
                try{
                    call_user_func_array($call,$args);
                }catch (\Throwable $throwable){
                    trigger_error($throwable->getMessage().$throwable->getTraceAsString());
                }
            }
        }
    }
}