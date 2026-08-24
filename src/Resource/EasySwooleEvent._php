<?php


namespace EasySwoole\EasySwoole;


use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use Swoole\Coroutine;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
        Coroutine::set(['hook_flags' => SWOOLE_HOOK_TCP]);
    }

    public static function mainServerCreate(EventRegister $register)
    {

    }
}