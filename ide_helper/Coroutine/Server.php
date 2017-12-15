<?php
/**
 * swoole-ide-helper
 * Author: Wudi <wudi@51idc.com>
 * Datetime: 20/07/2017
 */

namespace Swoole\Coroutine;


class Server
{
    /**
     * @link https://wiki.swoole.com/wiki/page/606.html
     * @return bool
     */
    public function getDefer()
    {
        return true;
    }


    /**
     * @link https://wiki.swoole.com/wiki/page/607.html
     * @param bool $is_defer
     */
    public function setDefer($is_defer = true)
    {

    }

    /**
     * 获取延迟收包的结果
     * @link https://wiki.swoole.com/wiki/page/608.html
     * @return mixed 当没有进行延迟收包或者收包超时，返回false。
     */
    public function recv()
    {

    }

    /**
     * 创新协程
     * @link https://wiki.swoole.com/wiki/page/687.html
     * @param callable $function
     */
    public static function create(callable $function)
    {

    }

    /**
     * 获取当前协程的ID
     * @link https://wiki.swoole.com/wiki/page/688.html
     * @return string 是一个20字节长的随机字符串
     */
    public function getuid()
    {

    }

}