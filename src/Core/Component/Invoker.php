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
use \Swoole\Async;

class Invoker
{
    /*
     *  Async::set([
          'enable_signalfd' => false,
       ]);
     */
    public static function exec(callable $callable,$timeOut = 100 * 1000)
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGALRM, function () {
            Process::alarm(-1);
            throw new \RuntimeException('func timeout');
        });
        try
        {
            Process::alarm($timeOut);
            $ret = call_user_func($callable);
            Process::alarm(-1);
            return $ret;
        }
        catch(\Throwable $throwable)
        {
            throw $throwable;
        }
    }

}