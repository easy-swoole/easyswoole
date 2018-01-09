<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/9
 * Time: 下午5:16
 */

declare(ticks = 1);
namespace EasySwoole\Core\Component;


use EasySwoole\Core\Swoole\ServerManager;

class Invoker
{
    private static $instance;
    /*
    * 如果需要用到invoker,请在框架初始化的时候，先获取一个Invoker，用以注册
    */
    public static function getInstance()
    {
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        return self::$instance;
    }


    final function __construct()
    {
        if(!ServerManager::getInstance()->isStart()){
            \Swoole\Async::set([
                'enable_signalfd' => false,
            ]);
        }
    }

    public function exec(callable $callable,$timeOut = 100 * 1000)
    {
        pcntl_signal(SIGALRM, function () {
            \Swoole\Process::alarm(-1);
            throw new \RuntimeException('func timeout');
        });
        try
        {
            \Swoole\Process::alarm($timeOut);
            $ret = call_user_func($callable);
            \Swoole\Process::alarm(-1);
            return $ret;
        }
        catch(\Throwable $throwable)
        {
            throw $throwable;
        }
    }

}