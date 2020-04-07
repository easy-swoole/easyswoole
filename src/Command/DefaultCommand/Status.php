<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Bridge\BridgeCommand;
use EasySwoole\EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use Swoole\Coroutine\Scheduler;

class Status implements CommandInterface
{

    public function commandName(): string
    {
        return "status";
    }

    public function exec(array $args): ?string
    {
        $ret = '';
        $run = new Scheduler();
        $run->add(function () use (&$ret, $args) {
            $package = new Package();
            $package->setCommand(BridgeCommand::SERVER_STATUS_INFO);
            $package = Bridge::getInstance()->send($package);
            if ($package->getStatus() !== Package::STATUS_SUCCESS) {
                return $package->getArgs();
            }
            if (empty($package->getArgs())) {
                return "server status info is abnormal";
            }
            $data = $package->getArgs();
            $data['start_time'] = date('Y-m-d h:i:s', $data['start_time']);
            $result = '';
            foreach ($data as $key => $val) {
                $result .= Utility::displayItem($key, $val) . "\n";
            }
            $ret = $result;
        });
        $run->start();
        return $ret;
    }

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo . <<<HELP
php easyswoole server status
HELP;
    }
}
