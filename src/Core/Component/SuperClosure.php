<?php
/**
 * Created by PhpStorm.
 * User: YF
 * Date: 2017/2/8
 * Time: 14:44
 */

namespace Core\Component;
use SuperClosure\Serializer;

class SuperClosure
{
    protected $func;
    protected $serializer;
    protected $serializedJson;
    protected $isSerialized = 0;
    function __construct(\Closure $func)
    {
        $this->func = $func;
        $this->serializer = new Serializer();
    }
    function __sleep()
    {
        // TODO: Implement __sleep() method.
        $this->serializedJson = $this->serializer->serialize($this->func);
        $this->isSerialized = 1;
        return array("serializedJson",'isSerialized');
    }
    function __wakeup()
    {
        // TODO: Implement __wakeup() method.
        $this->serializer = new Serializer();
        $this->func = $this->serializer->unserialize($this->serializedJson);
    }
    function __invoke()
    {
        // TODO: Implement __invoke() method.
        /*
         * prevent call before serialized
         */
        $args = func_get_args();
        if($this->isSerialized){
            $func = $this->serializer->unserialize($this->serializedJson);
        }else{
            $func = $this->func;
        }
        return  call_user_func_array($func,$args);
    }
}