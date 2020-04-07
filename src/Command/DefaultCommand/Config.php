<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use Co\Scheduler;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Bridge\BridgeCommand;
use EasySwoole\EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config as GlobalConfig;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\SysConst;

class Config implements CommandInterface
{

    public function commandName(): string
    {
        return 'config';
    }

    public function exec(array $args): ?string
    {
        $ret = '';
        $run = new Scheduler();
        $run->add(function () use (&$ret, $args) {
            $action = array_shift($args);
            switch ($action) {
                case 'show':
                    $key = array_shift($args);
                    $result = $this->show($key);
                    break;
                case 'set':
                    $key = array_shift($args);
                    $value = array_shift($args);
                    $result = $this->set($key,$value);
                    break;
                default:
                    $result = $this->help($args);
                    break;
            }
            $ret = $result;
        });
        $run->start();
        return $ret;


        return $response;
    }

    protected function show($key)
    {
        $package = new Package();
        $package->setCommand(BridgeCommand::CONFIG_INFO);
        $package->setArgs(['key'=>$key]);
        $package = Bridge::getInstance()->send($package);

        return var_export($package->getArgs(),1);
    }

    protected function set($key,$value){
        $package = new Package();
        $package->setCommand(BridgeCommand::CONFIG_SET);
        $package->setArgs(['key'=>$key,'value'=>$value]);
        $package = Bridge::getInstance()->send($package);
        return $package->getArgs();
    }

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo . "
php easyswoole config show [key][.key]
php easyswoole config set key value
";
    }
}
