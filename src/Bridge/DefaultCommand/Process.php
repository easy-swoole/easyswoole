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
        $array = Manager::getInstance()->info();
        foreach ($array as &$value){
            unset($value['hash']);
        }
        $responsePackage->setArgs($array);
        return true;
    }
}
