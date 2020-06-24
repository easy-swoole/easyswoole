<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Bridge\AbstractCommand;
use EasySwoole\EasySwoole\ServerManager;

class Status extends AbstractCommand
{
    public function commandName(): string
    {
        return 'status';
    }

    protected function info(Package $package, Package $responsePackage)
    {
        $responsePackage->setArgs(ServerManager::getInstance()->getSwooleServer()->stats());
        return true;
    }
}
