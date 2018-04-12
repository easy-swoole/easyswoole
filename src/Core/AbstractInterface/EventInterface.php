<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/12/27
 * Time: 下午2:56
 */

namespace EasySwoole\Core\AbstractInterface;


use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;
use EasySwoole\Core\Swoole\EventRegister;
use EasySwoole\Core\Swoole\ServerManager;

interface EventInterface
{
    public static function frameInitialize():void;

    public static function mainServerCreate(ServerManager $server,EventRegister $register):void;

    public static function onRequest(Request $request,Response $response):void;

    public static function afterAction(Request $request,Response $response):void;
}