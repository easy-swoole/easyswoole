<?php


namespace EasySwoole\EasySwoole\Command\DefaultCommand;


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
        $response = Utility::easySwooleLog();
        $mode = 'develop';
        if (!Core::getInstance()->isDev()) {
            $mode = 'produce';
        }
        $conf = GlobalConfig::getInstance();
        if (in_array("d", $args) || in_array("daemonize", $args)) {
            $conf->setConf("MAIN_SERVER.SETTING.daemonize", true);
        }
        //create main Server
        $serverType = $conf->getConf('MAIN_SERVER.SERVER_TYPE');
        switch ($serverType) {
            case EASYSWOOLE_SERVER:
            {
                $serverType = 'SWOOLE_SERVER';
                break;
            }
            case EASYSWOOLE_WEB_SERVER:
            {
                $serverType = 'SWOOLE_WEB';
                break;
            }
            case EASYSWOOLE_WEB_SOCKET_SERVER:
            {
                $serverType = 'SWOOLE_WEB_SOCKET';
                break;
            }
            case EASYSWOOLE_REDIS_SERVER:
            {
                $serverType = 'SWOOLE_REDIS';
                break;
            }
            default:
            {
                $serverType = 'UNKNOWN';
            }
        }
        $response = $response . Utility::displayItem('main server', $serverType) . "\n";
        $response = $response . Utility::displayItem('listen address', $conf->getConf('MAIN_SERVER.LISTEN_ADDRESS')) . "\n";
        $response = $response . Utility::displayItem('listen port', $conf->getConf('MAIN_SERVER.PORT')) . "\n";
        $ips = swoole_get_local_ip();
        foreach ($ips as $eth => $val) {
            $response = $response . Utility::displayItem('ip@' . $eth, $val) . "\n";
        }

        $data = $conf->getConf('MAIN_SERVER.SETTING');
        if(empty($data['user'])){
            $data['user'] = get_current_user();
        }

        if(!isset($data['daemonize'])){
            $data['daemonize'] = false;
        }

        foreach ($data as $key => $datum){
            $response = $response . Utility::displayItem($key,$datum) . "\n";
        }

        $response = $response . Utility::displayItem('swoole version', phpversion('swoole')) . "\n";
        $response = $response . Utility::displayItem('php version', phpversion()) . "\n";
        $response = $response . Utility::displayItem('easy swoole', SysConst::EASYSWOOLE_VERSION) . "\n";
        $response = $response . Utility::displayItem('develop/produce', $mode) . "\n";
        $response = $response . Utility::displayItem('temp dir', EASYSWOOLE_TEMP_DIR) . "\n";
        $response = $response . Utility::displayItem('log dir', EASYSWOOLE_LOG_DIR) . "\n";
        return $response;
    }

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return  $logo.'php easyswoole config';
    }
}