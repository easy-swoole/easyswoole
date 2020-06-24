<?php


namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Bridge\AbstractCommand;
use EasySwoole\EasySwoole\Config as GlobalConfig;
use EasySwoole\EasySwoole\Core;

class Config extends AbstractCommand
{
    public function commandName(): string
    {
        return 'config';
    }

    protected function info(Package $package, Package $response)
    {
        $data = $package->getArgs();
        if (empty($data['key'])) {
            $configArray = GlobalConfig::getInstance()->toArray();
            $configArray['mode'] = Core::getInstance()->isDev() ? 'develop' : 'produce';
        } else {
            $configArray = GlobalConfig::getInstance()->getConf($data['key']);
            $configArray = [$data['key'] => $configArray];
        }
        $response->setArgs($configArray);
        return true;
    }

    protected function set(Package $package, Package $response)
    {
        $data = $package->getArgs();
        if (empty($data['key'])) {
            $response->setMsg("config key can not be null");
            $response->setStatus($response::STATUS_COMMAND_ERROR);
            return false;
        }
        $key = $data['key'];
        $value = $data['value'] ?? null;
        GlobalConfig::getInstance()->setConf($key, $value);
        $response->setArgs([$key => $value]);
        return true;
    }
}
