<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2020/4/7 0007
 * Time: 15:51
 */

namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\EasySwoole\Bridge\BridgeCommand;
use EasySwoole\EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Config as GlobalConfig;

class Config extends Base
{
    static function initCommand(BridgeCommand $command)
    {
        $command->set(BridgeCommand::CONFIG_INFO, [Config::class, 'info']);
        $command->set(BridgeCommand::CONFIG_SET, [Config::class, 'set']);
    }

    static function info(Package $package,Package $response)
    {
        $data = $package->getArgs();
        if (empty($data['key'])){
            $configArray = GlobalConfig::getInstance()->toArray();
            $configArray['mode'] = Core::getInstance()->isDev() ? 'develop' : 'produce';
        }else{
            $configArray = GlobalConfig::getInstance()->getConf($data['key']);
        }
        $response->setArgs($configArray);
    }

    static function set(Package $package){
        $data = $package->getArgs();
        if (empty($data['key'])){
            return "config key can not be null";
        }
        $key = $data['key'];
        $value = $data['value']??null;
        GlobalConfig::getInstance()->setConf($key,$value);
        return "set up {$key}={$value} success";
    }
}
