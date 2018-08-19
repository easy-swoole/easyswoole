<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/7/29
 * Time: 上午11:40
 */

namespace EasySwoole\EasySwoole\Swoole\Time;


use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Trigger;

class Timer
{
    public static function loop($microSeconds,callable $func,$args = null){
        $new = function (...$args)use($func){
            try{
                call_user_func($func,...$args);
            }catch (\Throwable $throwable){
                Trigger::getInstance()->throwable($throwable);
            }
        };
        return ServerManager::getInstance()->getSwooleServer()->tick($microSeconds,$new,$args);
    }

    public static function delay($microSeconds,callable $func,$args = null){
        $new = function (...$args)use($func){
            try{
                call_user_func($func,...$args);
            }catch (\Throwable $throwable){
                Trigger::getInstance()->throwable($throwable);
            }
        };
        return ServerManager::getInstance()->getSwooleServer()->after($microSeconds,$new,$args);
    }
    /*
     * @param $timerId
     */
    public static function clear($timerId){
        ServerManager::getInstance()->getSwooleServer()->clearTimer($timerId);
    }
}