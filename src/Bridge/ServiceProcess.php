<?php


namespace EasySwoole\EasySwoole\Bridge;


use EasySwoole\Component\Process\Socket\AbstractUnixProcess;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Socket\Tools\Protocol;
use Swoole\Coroutine\Socket;

class ServiceProcess extends AbstractUnixProcess
{
    function onAccept(Socket $socket)
    {
        $data = Protocol::socketReader($socket,3,false);
        if($data){
            $package = unserialize($data);
        }
        $socket->close();
    }

    protected function onException(\Throwable $throwable, ...$args)
    {
        Trigger::getInstance()->throwable($throwable);
    }
}