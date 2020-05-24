<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\CommandInterface;
use EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Config as GlobalConfig;
use EasySwoole\EasySwoole\Core;
use Swoole\Coroutine\Socket;

class Config implements CommandInterface
{
    public function commandName(): string
    {
        return 'config';
    }

    public function exec(Package $package, Package $responsePackage, Socket $socket)
    {
        $action = $package->getArgs()['action'] ?? '';
        if (!method_exists($this, $action)) {
            $responsePackage->setStatus($responsePackage::STATUS_COMMAND_NOT_EXIST);
            $responsePackage->setMsg("command action:{$action} not empty");
            return $responsePackage;
        }
        $this->$action($package, $responsePackage);
    }


    function info(Package $package,Package $response)
    {
        $data = $package->getArgs();
        if (empty($data['key'])){
            $configArray = GlobalConfig::getInstance()->toArray();
            $configArray['mode'] = Core::getInstance()->isDev() ? 'develop' : 'produce';
        }else{
            $configArray = GlobalConfig::getInstance()->getConf($data['key']);
            $configArray = [$data['key']=>$configArray];
        }
        $response->setArgs($configArray);
        return true;
    }

    function set(Package $package,Package $response){
        $data = $package->getArgs();
        if (empty($data['key'])){
            $response->setMsg( "config key can not be null");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        $key = $data['key'];
        $value = $data['value']??null;
        GlobalConfig::getInstance()->setConf($key,$value);
        $response->setArgs([$key=>$value]);
        return true;
    }
}
