<?php


namespace EasySwoole\EasySwoole\AbstractInterface;


use EasySwoole\EasySwoole\Swoole\EventRegister;

interface Event
{
    public static function initialize();

    public static function mainServerCreate(EventRegister $register);

}