<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Core;
use EasySwoole\Utility\ArrayToTextTable;
use Swoole\Coroutine\Scheduler;

class Process implements CommandInterface
{
    public function commandName(): string
    {
        return 'process';
    }


    public function desc(): string
    {
        return 'Process manager';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('kill', 'kill process');
        $commandHelp->addAction('killAll', 'kill all processes');
        $commandHelp->addAction('show', 'show all process information');
        $commandHelp->addActionOpt('--pid=PID', 'kill the specified pid');
        $commandHelp->addActionOpt('--group=GROUP_NAME', 'kill the specified process group');
        $commandHelp->addActionOpt('-f', 'force kill process');
        $commandHelp->addActionOpt('-d', 'display in mb format');
        return $commandHelp;
    }

    public function exec(): string
    {
        $run = new Scheduler();
        $action = CommandManager::getInstance()->getArg(0);
        $run->add(function () use (&$result, $action) {
            Core::getInstance()->initialize();

            $package = Bridge::getInstance()->call($this->commandName(), ['action' => 'info']);

            if ($package->getStatus() != \EasySwoole\Bridge\Package::STATUS_SUCCESS) {
                return Color::error($package->getMsg());
            }

            $data = $this->processInfoHandel($package->getArgs());

            if (method_exists($this, $action)) {
                $result = $this->$action($data);
            }
        });
        $run->start();
        return $result ?? '';
    }

    protected function killProcess(array $list)
    {
        if (empty($list)) {
            return Color::error('not process was kill');
        }

        if (CommandManager::getInstance()->issetOpt('f')) {
            $sig = SIGKILL;
            $option = 'SIGKILL';
        } else {
            $sig = SIGTERM;
            $option = 'SIGTERM';
        }

        foreach ($list as $pid => $value) {
            \Swoole\Process::kill($pid, $sig);
            $list[$pid]['option'] = $option;
        }
        return new ArrayToTextTable($list);
    }

    protected function kill($json)
    {
        $list = [];
        $pid = CommandManager::getInstance()->getOpt('pid');
        $groupName = CommandManager::getInstance()->getOpt('group');
        foreach ($json as $key => $value) {
            if ($value['pid'] == $pid) {
                $list[$key] = $value;
            }

            if ($value['group'] == $groupName) {
                $list[$key] = $value;
            }
        }
        return $this->killProcess($list);
    }

    protected function killAll($json)
    {
        return $this->killProcess($json);
    }

    protected function show($json)
    {
        return new ArrayToTextTable($json);
    }

    protected function processInfoHandel($json)
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        if (CommandManager::getInstance()->issetOpt('d')) {
            foreach ($json as $key => $value) {
                $json[$key]['memoryUsage'] = round($value['memoryUsage'] / pow(1024, ($i = floor(log($value['memoryUsage'], 1024)))), 2) . ' ' . $unit[$i];
                $json[$key]['memoryPeakUsage'] = round($value['memoryPeakUsage'] / pow(1024, ($i = floor(log($value['memoryPeakUsage'], 1024)))), 2) . ' ' . $unit[$i];
            }
        }

        return $json;
    }
}
