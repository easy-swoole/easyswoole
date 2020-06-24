<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Bridge\AbstractCommand;
use EasySwoole\EasySwoole\Task\TaskManager;

class Task extends AbstractCommand
{
    public function commandName(): string
    {
        return 'task';
    }

    protected function info(Package $package, Package $responsePackage)
    {
        $responsePackage->setArgs(TaskManager::getInstance()->status());
        return true;
    }

}
