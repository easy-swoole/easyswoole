<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/2/11
 * Time: 下午9:19
 */

namespace EasySwoole\Core\Swoole\Memory;


use EasySwoole\Core\AbstractInterface\Singleton;
use Swoole\Atomic;

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
            $a = new Swoole\Atomic\Long($int);
            $this->listForLong[$name] = $a;
        }
    }

    function getLong($name):?Swoole\Atomic\Long
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