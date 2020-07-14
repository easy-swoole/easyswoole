<?php

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Core;
use EasySwoole\Utility\ArrayToTextTable;
use Swoole\Coroutine\Scheduler;

class Crontab implements CommandInterface
{
    public function commandName(): string
    {
        return 'crontab';
    }

    public function desc(): string
    {
        return 'Crontab manager';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('show', 'show crontab');
        $commandHelp->addAction('stop', 'stop crontab');
        $commandHelp->addAction('resume', 'resume crontab');
        $commandHelp->addAction('run', 'run crontab');
        $commandHelp->addActionOpt('--name', 'the taskname to be operated on');
        return $commandHelp;
    }

    public function exec(): string
    {
        $action = CommandManager::getInstance()->getArg(0);
        $run = new Scheduler();
        $run->add(function () use (&$result, $action) {
            if (method_exists($this, $action)) {
                Core::getInstance()->initialize();
                $result = $this->{$action}();
            }
        });
        $run->start();
        return $result ?? '';
    }

    protected function stop()
    {
        $taskName = CommandManager::getInstance()->getOpt('name');
        return Utility::bridgeCall($this->commandName(), function (Package $package) {
            $data = $package->getMsg();
            return Color::success($data) . PHP_EOL . $this->show();
        }, 'stop', ['taskName' => $taskName]);
    }


    protected function resume()
    {
        $taskName = CommandManager::getInstance()->getOpt('name');
        return Utility::bridgeCall($this->commandName(), function (Package $package) {
            $data = $package->getMsg();
            return Color::success($data) . PHP_EOL . $this->show();
        }, 'resume', ['taskName' => $taskName]);
    }

    protected function run()
    {
        $taskName = CommandManager::getInstance()->getOpt('name');
        return Utility::bridgeCall($this->commandName(), function (Package $package) {
            $data = $package->getMsg();
            return Color::success($data) . PHP_EOL . $this->show();
        }, 'run', ['taskName' => $taskName]);
    }

    protected function show()
    {
        return Utility::bridgeCall($this->commandName(), function (Package $package) {
            $data = $package->getArgs();
            foreach ($data as $k => $v) {
                $v['taskNextRunTime'] = date('Y-m-d H:i:s', $v['taskNextRunTime']);
                $data[$k] = array_merge(['taskName' => $k], $v);
            }
            return new ArrayToTextTable($data);
        }, 'show');
    }

}
