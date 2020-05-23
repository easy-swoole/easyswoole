<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\CommandInterface;
use EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Task\TaskManager;
use Swoole\Coroutine\Socket;

class Task implements CommandInterface
{
    public function commandName(): string
    {
        return 'task';
    }

    public function exec(Package $package,Package $responsePackage,Socket $socket)
    {
        $responsePackage->setArgs(TaskManager::getInstance()->status());
    }

}
