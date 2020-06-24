<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\Utility\ArrayToTextTable;

class Task extends AbstractCommand
{
    protected $helps = [
        'task status'
    ];

    public function commandName(): string
    {
        return 'task';
    }

    protected function status()
    {
        return $this->bridgeCall(function (Package $package, Result $result) {
            $result->setMsg(new ArrayToTextTable($package->getArgs()));
        }, 'info');
    }
}

