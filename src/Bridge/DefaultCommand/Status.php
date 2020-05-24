<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\CommandInterface;
use EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\ServerManager;
use Swoole\Coroutine\Socket;

class Status implements CommandInterface
{
    public function commandName(): string
    {
        return 'status';
    }

    public function exec(Package $package, Package $responsePackage, Socket $socket)
    {
        $responsePackage->setArgs(ServerManager::getInstance()->getSwooleServer()->stats());
        return true;
    }
}
