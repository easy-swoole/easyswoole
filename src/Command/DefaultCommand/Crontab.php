<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\CommandManager;
use EasySwoole\EasySwoole\Command\Utility;
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
        return 'crontab manager';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addCommand('show', 'show crontab');
        $commandHelp->addCommand('stop', 'stop crontab');
        $commandHelp->addCommand('resume', 'resume crontab');
        $commandHelp->addCommand('run', 'run crontab');
        return $commandHelp;
    }

    public function exec(): string
    {
        $args = CommandManager::getInstance()->getArgs();
        $run = new Scheduler();
        $run->add(function () use (&$result, $args) {
            $action = array_shift($args);
            if (method_exists($this, $action)) {
                $result = $this->{$action}($args);
            } else {
                $result = '';
            }
        });
        $run->start();
        return $result;
    }

    protected function stop($args)
    {
        $taskName = array_shift($args);
        return Utility::bridgeCall($this->commandName(), function (Package $package) {
            $data = $package->getMsg();
            return $data . PHP_EOL . $this->show()->getMsg();
        }, 'stop', ['taskName' => $taskName]);
    }


    protected function resume($args)
    {
        $taskName = array_shift($args);
        return Utility::bridgeCall($this->commandName(), function (Package $package) {
            $data = $package->getMsg();
            return $data . PHP_EOL . $this->show()->getMsg();
        }, 'resume', ['taskName' => $taskName]);
    }

    protected function run($args)
    {
        $taskName = array_shift($args);
        return Utility::bridgeCall($this->commandName(), function (Package $package) {
            $data = $package->getMsg();
            return $data . PHP_EOL . $this->show()->getMsg();
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
