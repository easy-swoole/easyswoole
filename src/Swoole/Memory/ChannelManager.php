<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/28
 * Time: 下午5:41
 */

namespace EasySwoole\EasySwoole\Swoole\Memory;


use EasySwoole\Component\Singleton;
use Swoole\Coroutine\Channel;

class ChannelManager
{
    use Singleton;
    private $list = [];

    function add($name,$size = 1024 * 256):void
    {
        if(!isset($this->list[$name])){
            $chan = new Channel($size);
            $this->list[$name] = $chan;
        }
    }

    function get($name):?Channel
    {
        if(isset($this->list[$name])){
            return $this->list[$name];
        }else{
            return null;
        }
    }
}