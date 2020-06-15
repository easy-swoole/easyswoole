<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/28
 * Time: 下午5:38
 */

namespace EasySwoole\EasySwoole\Swoole;


class EventHelper
{
    public static function register(EventRegister $register,string $event,callable $callback):void
    {
        $register->set($event,$callback);
    }

    public static function registerWithAdd(EventRegister $register,string $event,callable $callback):void
    {
        $register->add($event,$callback);
    }

    public static function on(\Swoole\Server $server,string $event,callable $callback)
    {
        $server->on($event,$callback);
    }
}