<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\ArrayToTextTable;
use Swoole\Coroutine\Scheduler;

class Process implements CommandInterface
{

    public function commandName(): string
    {
        return 'process';
    }

    function exec($args): ResultInterface
    {
        $result = new Result();
        $run = new Scheduler();
        $run->add(function () use (&$result, $args) {
            $result = new Result();
            $package = Bridge::getInstance()->call('process');
            if ($package->getStatus() == \EasySwoole\Bridge\Package::STATUS_SUCCESS) {
                $data = $package->getArgs();
            } else {
                $result->setMsg($package->getMsg());
                return $result;
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
                default:
                    $result = $this->help($args);
                    break;
            }
        });
        $run->start();
        return $result;
    }

    protected function killProcess(array $list, $args = null)
    {
        $result = new Result();
        if (empty($list)) {
            $result->setMsg('not process was kill');
            return $result;
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
        $result->setMsg(new ArrayToTextTable($list));
        return $result;
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
        $result = new Result();
        $result->setMsg(new ArrayToTextTable($json));
        return $result;
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

    function help($args): ResultInterface
    {
        $result = new Result();
        $logo = Utility::easySwooleLog();
        $msg = $logo . "
php easyswoole process kill PID [-p] [-d]
php easyswoole process kill PID [-f] [-p] [-d]
php easyswoole process kill GroupName [-f] [-d]
php easyswoole process killAll [-d]
php easyswoole process killAll [-f] [-d]
php easyswoole process show
php easyswoole process show [-d]
";
        $result->setMsg($msg);
        return $result;
    }
}
