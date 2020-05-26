<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\ArrayToTextTable;
use Swoole\Coroutine\Scheduler;

class Task implements CommandInterface
{

    public function commandName(): string
    {
        return 'task';
    }

    public function exec($args): ResultInterface
    {
        $run = new Scheduler();
        $result = new Result();
        $run->add(function () use (&$result, $args) {
            $action = array_shift($args);
            switch ($action) {
                case 'status':
                    $result = $this->status();
                    break;
                default:
                    $result = $this->help($args);
                    break;
            }
        });
        $run->start();
        return $result;
    }

    protected function status()
    {
        $result = new Result();
        $package = Bridge::getInstance()->call($this->commandName(), ['action' => 'info'], 3);
        if ($package->getStatus() == Package::STATUS_SUCCESS){
            $result->setMsg(new ArrayToTextTable($package->getArgs()));
        }else{
            $result->setMsg($package->getMsg());
        }
        return $result;
    }

    public function help($args): ResultInterface
    {
        $result = new Result();
        $logo = Utility::easySwooleLog();
        $msg = $logo . "
php easyswoole task status
";
        $result->setMsg($msg);
        return $result;
    }
}

