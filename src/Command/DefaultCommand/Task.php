<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Bridge\BridgeCommand;
use EasySwoole\EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Utility\ArrayToTextTable;

class Task implements CommandInterface
{

    public function commandName(): string
    {
        return 'task';
    }

    public function exec(array $args): ?string
    {
        $action = array_shift($args);
        switch ($action) {
            case 'status':
                $result = $this->status();
                break;
            default:
                $result = $this->help($args);
                break;
        }
        return $result;
    }

    protected function status()
    {
        try {
            $package = new Package();
            $package->setCommand(BridgeCommand::TASK_INFO);
            $package = Bridge::getInstance()->send($package);
            if (empty($package->getArgs())) {
                return "task info is abnormal";
            }
        } catch (\Throwable $exception) {
            return $exception->getMessage();
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
