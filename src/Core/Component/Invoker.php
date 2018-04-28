<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/9
 * Time: 下午5:16
 */

namespace EasySwoole\Core\Component;


use EasySwoole\Core\Swoole\ServerManager;
use \Swoole\Process;
use \Swoole\Coroutine;

class Invoker
{
    private static $swooleVersion = null;
    /*
     *  Async::set([
          'enable_signalfd' => false,
       ]);
     */
    public static function exec(callable $callable,$timeOut = 100 * 1000,...$params)
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGALRM, function () {
            Process::alarm(-1);
            throw new \RuntimeException('func timeout');
        });
        try
        {
            Process::alarm($timeOut);
            $ret = self::callUserFunc($callable,...$params);
            Process::alarm(-1);
            return $ret;
        }
        catch(\Throwable $throwable)
        {
            throw $throwable;
        }
    }


    public static function callUserFunc(callable $callable,...$params)
    {
        //只有swoole 2.x需要特殊处理
        if(self::getSwooleMainVersion() == 2){
            return Coroutine::call_user_func($callable,...$params);
        }else{
            return call_user_func($callable,...$params);
        }
    }

    public static function callUserFuncArray(callable $callable,array $params)
    {
        //只有swoole 2.x需要特殊处理
        if(self::getSwooleMainVersion() == 2){
            return Coroutine::call_user_func_array($callable,$params);
        }else{
            return call_user_func_array($callable,$params);
        }
    }

    private static function getSwooleMainVersion():int
    {
        if(self::$swooleVersion === null){
            self::$swooleVersion = intval(phpversion('swoole'));
        }
        return self::$swooleVersion;
    }
}