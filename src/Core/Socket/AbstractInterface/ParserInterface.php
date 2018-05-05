<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/22
 * Time: 下午1:00
 */

namespace EasySwoole\Core\Socket\AbstractInterface;

interface ParserInterface
{
    /*
     * 若返回EasySwoole\Core\Socket\Common\CommandBean，则为解析成功，
     * 若返回NULL，则调用parser error 回调
     */
    public static function decode($raw,$client);

    public static function encode(string $raw,$client):?string ;
}