<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\CommandManager;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\Utility\ArrayToTextTable;
use Swoole\Coroutine\Scheduler;

class Process extends AbstractCommand
{
    public function commandName(): string
    {
        return 'process';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addCommand('kill','kill process');
        $commandHelp->addCommand('killAll','killAll process');
        $commandHelp->addCommand('show','kill process');
        $commandHelp->addOpt('-p','kill process');
        $commandHelp->addOpt('-f','kill process');
        $commandHelp->addOpt('-d','kill process');
        return $commandHelp;
    }

    function exec(): string
    {
        $run = new Scheduler();
        $args = CommandManager::getInstance()->getArgs();
        $run->add(function () use (&$result, $args) {
            $package = Bridge::getInstance()->call($this->commandName(), ['action' => 'info']);
            if ($package->getStatus() == \EasySwoole\Bridge\Package::STATUS_SUCCESS) {
                $data = $package->getArgs();
            } else {
                $result = $package->getMsg();
            }
            $data = $this->processInfoHandel($data, $args);
            $action = array_shift($args);
            switch ($action) {
                case 'kill';
                    $result = $this->kill($data, $args);
                    break;
                case 'killAll';
                    $result = $this->killAll($data, $args);
                    break;
                case 'show';
                    $result = $this->show($data, $args);
                    break;
            }
        });
        $run->start();
        return $result;
    }

    protected function killProcess(array $list, $args = null)
    {
        if (empty($list)) {
            return 'not process was kill';
        }
        if (in_array('-f', $args)) {
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

    protected function kill($json, $args)
    {
        $pidOrGroupName = array_shift($args);
        $list = [];
        foreach ($json as $pid => $value) {
            if (in_array('-p', $args)) {
                if ($value['pid'] == $pidOrGroupName) {
                    $list[$pid] = $value;
                }
            } else {
                if ($value['group'] == $pidOrGroupName) {
                    $list[$pid] = $value;
                }
            }
        }
        return $this->killProcess($list, $args);
    }

    protected function killAll($json, $args)
    {
        $list = $json;
        return $this->killProcess($list, $args);
    }

    protected function show($json, $args)
    {
        return new ArrayToTextTable($json);
    }

    protected function processInfoHandel($json, $args)
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        if (in_array('-d', $args)) {
            foreach ($json as $key => $value) {
                $json[$key]['memoryUsage'] = round($value['memoryUsage'] / pow(1024, ($i = floor(log($value['memoryUsage'], 1024)))), 2) . ' ' . $unit[$i];
                $json[$key]['memoryPeakUsage'] = round($value['memoryPeakUsage'] / pow(1024, ($i = floor(log($value['memoryPeakUsage'], 1024)))), 2) . ' ' . $unit[$i];
            }
        }

        return $json;
    }
}
