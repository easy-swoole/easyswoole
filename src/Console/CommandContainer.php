<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/10/24
 * Time: 下午2:25
 */

namespace EasySwoole\EasySwoole\Console;


use EasySwoole\Component\Singleton;

class CommandContainer
{
    use Singleton;
    
    private $container = [];

    public function set($key,CommandInterface $command)
    {
        $this->container[$key] = $command;
    }

    function get($key):?CommandInterface
    {
        if(isset($this->container[$key])){
            return $this->container[$key];
        }else{
            return null;
        }
    }
}