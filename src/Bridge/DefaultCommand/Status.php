<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Bridge\AbstractCommand;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Task\TaskManager;

class Status extends AbstractCommand
{
    public function commandName(): string
    {
        return 'status';
    }

    protected function server(Package $package, Package $responsePackage)
    {
        $data = ServerManager::getInstance()->getSwooleServer()->stats();
        $data['runMode'] = Core::getInstance()->runMode();
        $responsePackage->setArgs($data);
        return true;
    }

    protected function task(Package $package, Package $responsePackage)
    {
        $responsePackage->setArgs(TaskManager::getInstance()->status());
        return true;
    }
}
