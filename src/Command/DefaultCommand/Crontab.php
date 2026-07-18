<?php

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\Command\AbstractInterface\AbstractCommand;
use EasySwoole\Command\Bean\Action;
use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\ExecStatusEnum;
use EasySwoole\Command\Bean\Option;
use EasySwoole\Command\Bean\Result;
use EasySwoole\Command\Color;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Core;
use EasySwoole\Utility\ArrayToTextTable;
use Swoole\Coroutine\Scheduler;

class Crontab extends AbstractCommand
{
    function name(): string
    {
        return 'crontab';
    }

    function description(): string
    {
        return 'EasySwoole crontab manager';
    }

    public function beforeExecute(Caller $caller, Result $result): bool
    {
        $mode = $caller->commandLine->getOption('mode');
        Core::getInstance()->initialize($mode);
        return true;
    }

    protected function init():void
    {
        $action = new Action('show','show crontab task info');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->setCallback(function (Caller $caller, Result $result) {
            $scheduler = new Scheduler();
            $scheduler->add(function ()use(&$result){
                $package = Bridge::bridgeCall( 'crontabInfo');
                if($package->getStatus() == StatusEnum::SUCCESS){
                    $result->result = $package->getArgs();
                }else{
                    $result->msg = Color::error($package->getMsg());
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                }
            });
            $scheduler->start();
            if($result->status == ExecStatusEnum::OK){
                $data = $result->result;
                foreach ($data as $k => $v) {
                    $v['taskNextRunTime'] = date('Y-m-d H:i:s', $v['taskNextRunTime']);
                    if($v['taskCurrentRunTime'] < 1024){
                        $v['taskCurrentRunTime'] = '-';
                    }else{
                        $v['taskCurrentRunTime'] = date('Y-m-d H:i:s', $v['taskCurrentRunTime']);
                    }
                    $data[$k] = array_merge(['taskName' => $k], $v);
                }
                $result->msg = new ArrayToTextTable($data);
            }
        });
        $this->registerAction($action);


        $action = new Action('stop','stop an crontab task');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->addOption(new class('taskName','crontab task name,such as --taskName=checkAlive') extends Option {
            public static function validate(mixed $value, Caller $caller): bool|string
            {
                if(empty($value)){
                    return 'taskName must be set';
                }
                return true;
            }
        });
        $action->setCallback(function (Caller $caller, Result $result) {
            $scheduler = new Scheduler();
            $scheduler->add(function ()use(&$result,$caller){
                $package = Bridge::bridgeCall( 'stopCrontabRule',[
                    'taskName'=>$caller->commandLine->getOption('taskName'),
                ]);
                if($package->getStatus() == StatusEnum::SUCCESS){
                    $result->result = $package->getMsg();
                    $result->msg = $package->getMsg();
                }else{
                    $result->msg = Color::error($package->getMsg());
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                }
            });
            $scheduler->start();
        });
        $this->registerAction($action);


        $action = new Action('resume','resume an crontab task');
        $action->addOption(new Option('mode','run mode,such as --mode=dev'));
        $action->addOption(new class('taskName','crontab task name,such as --taskName=checkAlive') extends Option {
            public static function validate(mixed $value, Caller $caller): bool|string
            {
                if(empty($value)){
                    return 'taskName must be set';
                }
                return true;
            }
        });
        $action->setCallback(function (Caller $caller, Result $result) {
            $scheduler = new Scheduler();
            $scheduler->add(function ()use(&$result,$caller){
                $package = Bridge::bridgeCall( 'resumeCrontabRule',[
                    'taskName'=>$caller->commandLine->getOption('taskName'),
                ]);
                if($package->getStatus() == StatusEnum::SUCCESS){
                    $result->result = $package->getMsg();
                    $result->msg = $package->getMsg();
                }else{
                    $result->msg = Color::error($package->getMsg());
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                }
            });
            $scheduler->start();
        });
        $this->registerAction($action);
    }

}
