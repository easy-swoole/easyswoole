<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\Utility\ArrayToTextTable;

class Task extends AbstractCommand
{
    public function commandName(): string
    {
        return 'task';
    }

    public function help(): array
    {
        return [
            'status'
        ];
    }

    protected function status()
    {
        return $this->bridgeCall(function (Package $package, Result $result) {
            return new ArrayToTextTable($package->getArgs());
        }, 'info');
    }
}

