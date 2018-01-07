<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/2
 * Time: 下午5:16
 */

namespace EasySwoole\Core\Swoole;


class Timer
{
    public static function loop($microSeconds,\Closure $func,$args = null){
        return ServerManager::getInstance()->getServer()->tick($microSeconds,$func,$args);
    }
    public static function delay($microSeconds,\Closure $func,$args = null){
        ServerManager::getInstance()->getServer()->after($microSeconds,$func,$args);
    }
    /*
     * @param $timerId
     */
    public static function clear($timerId){
        ServerManager::getInstance()->getServer()->clearTimer($timerId);
    }
}