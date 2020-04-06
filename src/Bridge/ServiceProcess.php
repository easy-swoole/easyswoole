<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Component\Process\Socket\AbstractUnixProcess;
use EasySwoole\EasySwoole\Trigger;
use Swoole\Coroutine\Socket;

class ServiceProcess extends AbstractUnixProcess
{
    function onAccept(Socket $socket)
    {

    }

    protected function onException(\Throwable $throwable, ...$args)
    {
        Trigger::getInstance()->throwable($throwable);
    }
}