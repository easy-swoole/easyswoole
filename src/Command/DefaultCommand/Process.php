<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Bridge\StatusEnum;
use EasySwoole\Command\AbstractInterface\AbstractCommand;
use EasySwoole\Command\Bean\Action;
use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\ExecStatusEnum;
use EasySwoole\Command\Bean\Option;
use EasySwoole\Command\Bean\Result;
use EasySwoole\Command\Color;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Core;
use EasySwoole\Utility\ArrayToTextTable;
use Swoole\Coroutine\Scheduler;

class Process extends AbstractCommand
{
    function name(): string
    {
        return 'process';
    }

    function description(): string
    {
        return 'EasySwoole process manager';
    }

    public function beforeExecute(Caller $caller, Result $result): bool
    {
        $mode = $caller->commandLine->getOption('mode');
        Core::getInstance()->initialize($mode);
        return true;
    }

    protected function init(): void
    {
        $action = new Action('show','show EasySwoole process list');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->setCallback(function (Caller $caller,Result $result) {
            $scheduler = new Scheduler();
            $scheduler->add(function ()use(&$result){
                $package = Bridge::bridgeCall( 'processInfo');
                if($package->getStatus() == StatusEnum::SUCCESS){
                    $result->result = $package->getArgs();
                }else{
                    $result->msg = Color::error($package->getMsg());
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                }
            });
            $scheduler->start();
            if($result->status == ExecStatusEnum::OK){
                $result->msg = new ArrayToTextTable($this->processInfoHandel($result->result));
            }
        });
        $this->registerAction($action);

        $action = new Action('kill','kill EasySwoole process');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->addOption(new Option('pid','kill specified pid，such as --pid=9501'));
        $action->addOption(new Option('group','kill the specified group process，such as --group=Crontab'));
        $action->addOption(new Option('force','kill process with SIG_KILL'));
        $action->setCallback(function (Caller $caller,Result $result) {
            $scheduler = new Scheduler();
            $scheduler->add(function ()use(&$result){
                $package = Bridge::bridgeCall( 'processInfo');
                if($package->getStatus() == StatusEnum::SUCCESS){
                    $result->result = $package->getArgs();
                }else{
                    $result->msg = Color::error($package->getMsg());
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                }
            });
            $scheduler->start();
            if($result->status == ExecStatusEnum::OK){
                $force = $caller->commandLine->hasOption('force');
                $sig = SIGTERM;
                if($force){
                    $sig = SIGKILL;
                }
                $pid = $caller->commandLine->getOption('pid');
                $group = $caller->commandLine->getOption('group');
                $list = [];
                $allProcess = $result->result;
                foreach ($allProcess as $key => $value) {
                    if ($value['pid'] == $pid) {
                        $list[$key] = $value;
                    }

                    if ($value['group'] == $group) {
                        $list[$key] = $value;
                    }
                }
                foreach ($list as $pid => $value) {
                    \Swoole\Process::kill($pid, $sig);
                    if($sig == SIGKILL){
                        $list[$pid]['option'] = 'SIGKILL';
                    }else{
                        $list[$pid]['option'] = 'SIGTERM';
                    }
                    $list[$pid]['startUpTime'] = date('Y-m-d H:i:s', $value['startUpTime']);
                    unset($list[$pid]['memoryUsage']);
                    unset($list[$pid]['memoryPeakUsage']);
                    unset($list[$pid]['lastHeartBeat']);
                }
                $result->msg = new ArrayToTextTable($list);
            }
        });
        $this->registerAction($action);
    }

    protected function processInfoHandel($json)
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        foreach ($json as $key => $value) {
            $json[$key]['memoryUsage'] = round($value['memoryUsage'] / pow(1024, ($i = floor(log($value['memoryUsage'], 1024)))), 2) . ' ' . $unit[$i];
            $json[$key]['memoryPeakUsage'] = round($value['memoryPeakUsage'] / pow(1024, ($i = floor(log($value['memoryPeakUsage'], 1024)))), 2) . ' ' . $unit[$i];
            $json[$key]['startUpTime'] = date('Y-m-d H:i:s',$json[$key]['startUpTime']);
            $json[$key]['lastHeartBeat'] = date('Y-m-d H:i:s',$json[$key]['lastHeartBeat']);
        }

        return $json;
    }

}
