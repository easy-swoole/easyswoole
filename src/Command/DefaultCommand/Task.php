<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Bridge\Bridge;
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
        $result = new Result();
        $package = Bridge::getInstance()->call($this->commandName(), ['action' => 'info'], 3);
        if ($package->getStatus() == Package::STATUS_SUCCESS) {
            $result->setMsg(new ArrayToTextTable($package->getArgs()));
        } else {
            $result->setMsg($package->getMsg());
        }
        return $result;
    }
}

