<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:32
 */

namespace EasySwoole\EasySwoole\AbstractInterface;


use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

interface Event
{
    public static function initialize();

    public static function mainServerCreate(EventRegister $register);

    public static function onRequest(Request $request,Response $response):bool ;

    public static function afterRequest(Request $request,Response $response):void;
}