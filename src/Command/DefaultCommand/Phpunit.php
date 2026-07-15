<?php

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\CommandManager;
use PHPUnit\TextUI\Application;
use Swoole\Coroutine;
use Swoole\Coroutine\Scheduler;
use Swoole\Timer;

class Phpunit implements CommandInterface
{
    public function commandName(): string
    {
        return 'phpunit';
    }

    public function exec(): ?string
    {
        $argv = CommandManager::getInstance()->getOriginArgv();
        array_shift($argv);
        (new Application)->run($argv);

        return null;
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        return $commandHelp;
    }

    public function desc(): string
    {
        return 'PHP Unit Testing, Support Coroutine and No-Coroutine Testing.';
    }

}