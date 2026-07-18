<?php

namespace EasySwoole\EasySwoole\Bridge;

use EasySwoole\Bridge\Package;
use EasySwoole\Component\Process\Manager;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\ServerManager;

class BaseService extends AbstractCommand
{

    public function commandName(): string
    {
        return 'BaseService';
    }

    function serverStatus(Package $request,Package $responsePackage):void
    {
        $data = ServerManager::getInstance()->getSwooleServer()->stats();
        $data = Utility::createServerDisplayItem(Config::getInstance()) + $data;
        $responsePackage->setArgs($data);
    }

    function processInfo(Package $request,Package $responsePackage):void
    {
        $array = Manager::getInstance()->info();
        foreach ($array as &$value){
            unset($value['hash']);
        }
        $responsePackage->setArgs($array);
    }
}