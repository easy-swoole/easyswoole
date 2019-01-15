<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/29
 * Time: 上午11:34
 */

namespace EasySwoole\EasySwoole\Swoole\Task;
use SuperClosure\Serializer;

class SuperClosure
{
    private $closure;
    private $serialized;

    function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    final public function __sleep()
    {
        $serializer = new Serializer();
        $this->serialized = $serializer->serialize($this->closure);
        unset($this->closure);
        return ['serialized'];
    }

    final public function __wakeup()
    {
        $serializer = new Serializer();
        $this->closure = $serializer->unserialize($this->serialized);
    }

    final public function __invoke()
    {
        // TODO: Implement __invoke() method.
        $args = func_get_args();
        return call_user_func($this->closure,...$args);
    }

    final function call(...$args)
    {
        return call_user_func($this->closure,...$args);
    }
}