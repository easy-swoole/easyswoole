<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Bridge\BridgeCommand;
use EasySwoole\EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;

class Status implements CommandInterface
{

    public function commandName(): string
    {
        return "status";
    }

    public function exec(array $args): ?string
    {
        try {
            $package = new Package();
            $package->setCommand(BridgeCommand::SERVER_STATUS_INFO);
            $package = Bridge::getInstance()->send($package);
            if (empty($package->getArgs())) {
                return "server status info is abnormal";
            }
        } catch (\Throwable $throwable) {
            return $throwable->getMessage();
        }
        $data = $package->getArgs();
        $data['start_time'] = date('Y-m-d h:i:s', $data['start_time']);
        $ret = '';
        foreach ($data as $key => $val) {
            $ret .= Utility::displayItem($key, $val) . "\n";
        }
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
