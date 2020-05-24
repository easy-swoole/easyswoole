<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\CommandInterface;
use EasySwoole\Bridge\Package;
use EasySwoole\Component\Process\Manager;
use Swoole\Coroutine\Socket;

class Process implements CommandInterface
{
    public function commandName(): string
    {
        return 'process';
    }

    public function exec(Package $package, Package $responsePackage, Socket $socket)
    {
        $responsePackage->setArgs(Manager::getInstance()->info());
        return true;
    }
}
