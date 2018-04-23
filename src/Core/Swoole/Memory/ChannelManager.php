<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/18
 * Time: 下午12:35
 */

namespace EasySwoole\Core\Swoole\Memory;


use EasySwoole\Core\AbstractInterface\Singleton;
use Swoole\Channel;

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