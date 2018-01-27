<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/11
 * Time: 下午9:14
 */

namespace EasySwoole\Core\Swoole\Time;


use EasySwoole\Core\Swoole\ServerManager;

class Timer
{
    public static function loop($microSeconds,\Closure $func,$args = null){
        return ServerManager::getInstance()->getServer()->tick($microSeconds,$func,$args);
    }
    public static function delay($microSeconds,\Closure $func,$args = null){
       return  ServerManager::getInstance()->getServer()->after($microSeconds,$func,$args);
    }
    /*
     * @param $timerId
     */
    public static function clear($timerId){
        ServerManager::getInstance()->getServer()->clearTimer($timerId);
    }
}