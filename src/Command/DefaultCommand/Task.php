<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\CommandManager;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\ArrayToTextTable;
use Swoole\Coroutine\Scheduler;

class Task implements CommandInterface
{
    public function commandName(): string
    {
        return 'task';
    }

    public function desc(): string
    {
        return 'task manager';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addCommand('status', 'task status');
        return $commandHelp;
    }

    public function exec(): string
    {
        $args = CommandManager::getInstance()->getArgs();
        $run = new Scheduler();
        $run->add(function () use (&$result, $args) {
            $action = array_shift($args);
            if (method_exists($this, $action)) {
                $result = $this->{$action}($args);
            } else {
                $result = '';
            }
        });
        $run->start();
        return $result;
    }

    protected function status()
    {
        return Utility::bridgeCall($this->commandName(), function (Package $package, Result $result) {
            return new ArrayToTextTable($package->getArgs());
        }, 'info');
    }
}

