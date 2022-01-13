<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\CommandManager;
use EasySwoole\Component\WaitGroup;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Core;
use EasySwoole\Utility\ArrayToTextTable;
use Swoole\Coroutine;
use Swoole\Coroutine\Scheduler;

class Task implements CommandInterface
{
    public function commandName(): string
    {
        return 'task';
    }

    public function desc(): string
    {
        return 'Task manager';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('status', 'status of the task');
        $commandHelp->addAction('reboot', 'reboot all of task worker');
        return $commandHelp;
    }

    public function exec(): ?string
    {
        $action = CommandManager::getInstance()->getArg(0);
        Core::getInstance()->initialize();
        $run = new Scheduler();
        $run->add(function () use (&$result, $action) {
            if (method_exists($this, $action) && $action != 'help') {
                $result = $this->{$action}();
                return;
            }

            $result = CommandManager::getInstance()->displayCommandHelp($this->commandName());
        });
        $run->start();
        return $result;
    }

    private function reboot()
    {
        $list = Utility::bridgeCall('status', function (Package $package) {
            $data = $package->getArgs();
            if(empty($data)){
                return 'please check config item for task worker num';
            }
            return $data;
        }, 'task');

        if(is_array($list)){
            if (CommandManager::getInstance()->issetOpt('f')) {
                $sig = SIGKILL;
                $option = 'SIGKILL';
            } else {
                $sig = SIGTERM;
                $option = 'SIGTERM';
            }

            $ret = [];

            foreach ($list as $item){
                \Swoole\Process::kill($item['pid'], $sig);
                $ret[$item['pid']] = [
                    "pid"=>$item['pid'],
                    "startUpTime"=>date('Y-m-d H:i:s',$item['startUpTime']),
                    "signalTime"=>date('Y-m-d H:i:s'),
                    "signalType"=>$option,
                    "3s-Status"=>"alive"
                ];
            }

            //检测检测

            $wait = new WaitGroup();
            foreach ($ret as $pid =>$item){
                $wait->add();
                Coroutine::create(function ()use($pid,$wait,&$ret){
                    $start = time();
                    while (1){
                        if(time() - $start > 3){
                            $wait->done();
                            break;
                        }else{
                            if(!\Swoole\Process::kill($pid, 0)){
                                $ret[$pid]['3s-Status'] = "exit";
                                $wait->done();
                                break;
                            }
                        }
                        Coroutine::sleep(0.01);
                    }
                });
            }

            $wait->wait(3);

            return new ArrayToTextTable($ret);
        }else{
            return $list;
        }
    }

    protected function status()
    {
        return Utility::bridgeCall('status', function (Package $package) {
            $data = $package->getArgs();
            if(empty($data)){
                return 'please check config item for task worker num';
            }
            foreach ($data as $key => &$datum){
                $datum['workerIndex'] = $key;
                $datum['startUpTime'] = date('Y-m-d H:i:s',$datum['startUpTime']);
            }
            return new ArrayToTextTable($data);
        }, 'task');
    }
}

