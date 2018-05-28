<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/5/28
 * Time: 下午6:32
 */

namespace EasySwoole\Frame\AbstractInterface;


use EasySwoole\Core\EventRegister;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;

interface Event
{
    public static function initialize();

    public static function mainServerCreate(EventRegister $register);

    public static function onRequest(Request $request,Response $response):void;

    public static function afterAction(Request $request,Response $response):void;
}