<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Component\Process\Manager;
use EasySwoole\EasySwoole\Bridge\AbstractCommand;

class Process extends AbstractCommand
{
    public function commandName(): string
    {
        return 'process';
    }

    protected function info(Package $package, Package $responsePackage)
    {
        $responsePackage->setArgs(Manager::getInstance()->info());
        return true;
    }
}
