<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/4
 * Time: 下午3:52
 */

namespace EasySwoole;


use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;
use EasySwoole\Core\Swoole\EventRegister;

class Event
{
    public static function frameInitialize():void
    {

    }

    public static function swooleEventRegister(EventRegister $register):void
    {

    }

    public static function frameInitialized():void
    {

    }

    public static function onRequest(Request $request,Response $response):void
    {

    }

    public static function afterAction(Request $request,Response $response):void
    {

    }
}