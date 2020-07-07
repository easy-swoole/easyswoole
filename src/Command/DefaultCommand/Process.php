<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\CallerInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\Utility\ArrayToTextTable;
use Swoole\Coroutine\Scheduler;

class Process extends AbstractCommand
{
    protected $helps = [
        'process kill PID [-p] [-d]',
        'process kill PID [-f] [-p] [-d]',
        'process kill GroupName [-f] [-d]',
        'process killAll [-d]',
        'process killAll [-f] [-d]',
        'process show',
        'process show [-d]'
    ];

    public function commandName(): string
    {
        return 'process';
    }

    function exec(CallerInterface $caller): ResultInterface
    {
        $run = new Scheduler();
        $run->add(function () use (&$result, $caller) {
            $result = new Result();
            $package = Bridge::getInstance()->call($this->commandName(), ['action' => 'info']);
            if ($package->getStatus() == Package::STATUS_SUCCESS) {
                $data = $package->getArgs();
            } else {
                $result->setMsg($package->getMsg());
                return $result;
            }
            $data = $this->processInfoHandel($data, $caller);
            $action = key($caller->getOneParam());
            switch ($action) {
                case 'kill';
                    $result = $this->kill($data, $caller);
                    break;
                case 'killAll';
                    $result = $this->killAll($data, $caller);
                    break;
                case 'show';
                    $result = $this->show($data, $caller);
                    break;
                default:
                    $result = $this->help($caller);
                    break;
            }
        });
        $run->start();
        return $result;
    }

    protected function killProcess(array $list, CallerInterface $caller)
    {
        $result = new Result();
        if (empty($list)) {
            $result->setMsg('not process was kill');
            return $result;
        }
        if ($caller->getParams('-f')) {
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
        $result->setMsg(new ArrayToTextTable($list));
        return $result;
    }

    protected function kill($json, CallerInterface $caller)
    {
        $pidOrGroupName = key($caller->getOneParam());
        $list = [];
        foreach ($json as $pid => $value) {
            if ($caller->getParams('-p')) {
                if ($value['pid'] == $pidOrGroupName) {
                    $list[$pid] = $value;
                }
            } else {
                if ($value['group'] == $pidOrGroupName) {
                    $list[$pid] = $value;
                }
            }
        }
        return $this->killProcess($list, $caller);
    }

    protected function killAll($json, CallerInterface $caller)
    {
        $list = $json;
        return $this->killProcess($list, $caller);
    }

    protected function show($json, CallerInterface $caller)
    {
        $result = new Result();
        $result->setMsg(new ArrayToTextTable($json));
        return $result;
    }

    protected function processInfoHandel($json, CallerInterface $caller)
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        if ($caller->getParams('-d')) {
            foreach ($json as $key => $value) {
                $json[$key]['memoryUsage'] = round($value['memoryUsage'] / pow(1024, ($i = floor(log($value['memoryUsage'], 1024)))), 2) . ' ' . $unit[$i];
                $json[$key]['memoryPeakUsage'] = round($value['memoryPeakUsage'] / pow(1024, ($i = floor(log($value['memoryPeakUsage'], 1024)))), 2) . ' ' . $unit[$i];
            }
        }

        return $json;
    }
}
