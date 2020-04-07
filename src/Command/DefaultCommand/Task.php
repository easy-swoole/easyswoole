<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Bridge\BridgeCommand;
use EasySwoole\EasySwoole\Bridge\Package;
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

    public function exec(array $args): ?string
    {
        $ret = '';
        $run = new Scheduler();
        $run->add(function () use (&$ret, $args) {
            $action = array_shift($args);
            switch ($action) {
                case 'status':
                    $result = $this->status();
                    break;
                default:
                    $result = $this->help($args);
                    break;
            }
            $ret = $result;
        });
        $run->start();
        return $ret;
    }

    protected function status()
    {
        $package = new Package();
        $package->setCommand(BridgeCommand::TASK_INFO);
        $package = Bridge::getInstance()->send($package);
        if ($package->getStatus() !== Package::STATUS_SUCCESS) {
            return $package->getArgs();
        }
        if (empty($package->getArgs())) {
            return "task info is abnormal";
        }
        $data = $package->getArgs();

        $result = new  ArrayToTextTable($data);
        return $result;
    }

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo . "
php easyswoole task status
";
    }
}
