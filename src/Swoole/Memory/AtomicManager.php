<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/28
 * Time: 下午5:39
 */

namespace EasySwoole\EasySwoole\Swoole\Memory;


use EasySwoole\Component\Singleton;
use Swoole\Atomic;
use Swoole\Atomic\Long;


class AtomicManager
{
    use Singleton;

    private $list = [];
    private $listForLong = [];

    function add($name,int $int = 0):void
    {
        if(!isset($this->list[$name])){
            $a = new Atomic($int);
            $this->list[$name] = $a;
        }
    }

    function addLong($name,int $int = 0)
    {
        if(!isset($this->listForLong[$name])){
            $a = new Long($int);
            $this->listForLong[$name] = $a;
        }
    }

    function getLong($name):?Long
    {
        if(!isset($this->listForLong[$name])){
            return $this->listForLong[$name];
        }else{
            return null;
        }
    }

    function get($name):?Atomic
    {
        if(isset($this->list[$name])){
            return $this->list[$name];
        }else{
            return null;
        }
    }
}