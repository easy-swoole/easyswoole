<?php
/**
 * swoole-ide-helper
 * Author: Wudi <wudi@anchnet.com>
 * Datetime: 09/11/2017
 */

namespace Swoole;

class Coroutine
{
    /**
     * 创建一个新的协程，并立即执行
     *
     * @param callable $function 协程执行的代码
     * @return bool
     */
    public static function create(callable $function)
    {
        return true;
    }

    /**
     * 恢复某个协程，使其继续运行
     * 当前协程处于挂起状态时，另外的协程中可以使用resume再次唤醒当前协程
     * @link https://wiki.swoole.com/wiki/page/772.html
     * @param string $coroutineId 为要恢复的协程ID，在协程内可以使用getuid获取到协程的ID
     */
    public static function resume($coroutineId)
    {

    }

    /**
     * 挂起当前协程
     * @link https://wiki.swoole.com/wiki/page/773.html
     * @param string $corouindId 要挂起协程的ID
     */
    public static function suspend($corouindId)
    {

    }

    /**
     * 获取当前协程的唯一id 返回值： * 成功时返回当前协程ID（int） * 如果当前不在协程环境中，则返回-1
     *
     * @return integer
     */
    public static function getuid()
    {
        return 1;
    }

    /**
     * 协程版反射调用函数
     *
     * @param callable $callback
     * @param array $param_arr
     * @return mixed
     */
    public static function call_user_func_array(callable $callback, array $param_arr)
    {

    }

    /**
     * 协程版反射调用函数
     *
     * @param callable $callback
     * @param null $parameter [optional]
     * @param null $_ [optional]
     * @return mixed
     */
    public static function call_user_func(callable $callback, $parameter = null, $_ = null)
    {

    }

}