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
        if(SWOOLE_VERSION >1){
            self::call($callable, $params);
        }else{
            return call_user_func($callable,...$params);
        }
    }

    public static function callUserFuncArray(callable $callable,array $params)
    {
        if(SWOOLE_VERSION > 1){
            self::call($callable, $params);
        }else{
            return call_user_func_array($callable,$params);
        }
    }

    private static function call(callable $callable, array $params)
    {
        if($callable instanceof \Closure) {
            return $callable(...$params);
        } else if (is_array($callable)) {
            if (is_object($callable[0])) {
                $class = $callable[0];
                $method = $callable[1];
                // can't use parent::method ...
                return $class->$method(...$params);
            } else if (is_string($callable[0])) {
                $class = $callable[0];
                $method = $callable[1];
                if (strpos($method, '::') !== false) {
                    [$c, $method] = explode('::', $method, 2);
                    if ($c == 'parent') {
                        $class = get_parent_class($class);
                        if ($class === false) {
                            return null;
                        }
                    } else if ($c == 'self') {

                    } else {
                        return null;
                    }
                }
                return $class::$method(...$params);
            } else {
                return null;
            }
        } else if (is_string($callable)) {
            if (strpos($callable, '::') === false) {
                return $callable(...$params);
            } else {
                [$class, $method] = explode('::', $callable, 2);
                return $class::$method(...$params);
            }
        } else if (is_object($callable)) {
            return $callable->__invoke(...$params);
        } else {
            return null;
        }
    }
}