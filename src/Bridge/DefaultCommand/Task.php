<?php

namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;

use EasySwoole\Bridge\Package;
use EasySwoole\Component\Process\Manager;
use EasySwoole\EasySwoole\Bridge\AbstractCommand;

class Task extends AbstractCommand
{

    public function commandName(): string
    {
        return 'task';
    }

    protected function status(Package $package, Package $response)
    {
        $info = Manager::getInstance()->info('EasySwoole.dev.TaskWorker');
        var_dump($info);
        $package->setArgs([
            'a'=>111
        ]);
        return true;
    }
}