<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Bridge\AbstractCommand;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Task\TaskManager;

class Status extends AbstractCommand
{
    public function commandName(): string
    {
        return 'status';
    }

    protected function call(Package $package, Package $responsePackage)
    {
        $data = ServerManager::getInstance()->getSwooleServer()->stats();
        $data = Utility::createServerDisplayItem(Config::getInstance()) + $data;
        $responsePackage->setArgs($data);
        return true;
    }
}
