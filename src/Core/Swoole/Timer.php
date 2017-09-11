<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/1/31
 * Time: 下午12:33
 */

namespace Core\Swoole;


class Timer
{
    /*
     * onTimer中执行时间过长，会导致下一次定时延缓触发。如设定1秒的定时器，1秒后会触发onTimer，
     * onTimer函数用时1.5s，那么第二次触发onTimer的时间为第3秒。中间第2秒的定时器会被丢弃
        onTimer回调函数如果要执行一个耗时操作，最好是使用$serv->task投递到task进程池中执行
     * 1.8.0以上可分配给task worker
     * $microSeconds 最大不得超过 86400000 毫秒
     * @return timerId

    */
    static function loop($microSeconds,\Closure $func,$args = null){
        return Server::getInstance()->getServer()->tick($microSeconds,$func,$args);
    }
    static function delay($microSeconds,\Closure $func,$args = null){
        Server::getInstance()->getServer()->after($microSeconds,$func,$args);
    }
    /*
     * @param $timerId
     */
    static function clear($timerId){
        Server::getInstance()->getServer()->clearTimer($timerId);
    }
}