<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:44
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\SysConst;

class Start implements CommandInterface
{

    public function commandName(): string
    {
        // TODO: Implement commandName() method.
        return 'start';
    }

    public function exec(array $args): ?string
    {
        // TODO: Implement exec() method.
        Utility::opCacheClear();
        $response = Utility::easySwooleLog();
        $mode = 'develop';
        if(!Core::getInstance()->isDev()){
            $mode = 'produce';
        }
        $conf = Config::getInstance();
        if(in_array("d",$args) || in_array("daemonize",$args)){
            $conf->setConf("MAIN_SERVER.SETTING.daemonize", true);
        }
        //create main Server
        Core::getInstance()->createServer();
        $serverType = $conf->getConf('MAIN_SERVER.SERVER_TYPE');
        switch ($serverType){
            case EASYSWOOLE_SERVER:{
                $serverType = 'SWOOLE_SERVER';
                break;
            }
            case EASYSWOOLE_WEB_SERVER:{
                $serverType = 'SWOOLE_WEB';
                break;
            }
            case EASYSWOOLE_WEB_SOCKET_SERVER:{
                $serverType = 'SWOOLE_WEB_SOCKET';
                break;
            }
            case EASYSWOOLE_REDIS_SERVER:{
                $serverType = 'SWOOLE_REDIS';
                break;
            }
            default:{
                $serverType = 'UNKNOWN';
            }
        }
        $response = $response.Utility::displayItem('main server',$serverType)."\n";
        $response = $response.Utility::displayItem('listen address',$conf->getConf('MAIN_SERVER.LISTEN_ADDRESS'))."\n";
        $response = $response.Utility::displayItem('listen port', $conf->getConf('MAIN_SERVER.PORT'))."\n";
        $list  = ServerManager::getInstance()->getSubServerRegister();
        $index = 1;
        foreach ($list as $serverName => $item){
            if(empty($item['setting'])){
                $type = $serverType;
            }else{
                $type = $item['type'] % 2 > 0 ? 'SWOOLE_TCP' : 'SWOOLE_UDP';
            }
            $response = $response.Utility::displayItem("sub server:{$serverName}","{$type}@{$item['listenAddress']}:{$item['port']}")."\n";
            $index++;
        }
        $ips = swoole_get_local_ip();
        foreach ($ips as $eth => $val){
            $response = $response.Utility::displayItem('ip@'.$eth,$val)."\n";
        }
        $data = $conf->getConf('MAIN_SERVER.SETTING');
        foreach ($data as $key => $datum){
            $response = $response.Utility::displayItem($key, (string)$datum)."\n";
        }
        $user = $conf->getConf('MAIN_SERVER.SETTING.user');
        if(empty($user)){
            $user = get_current_user();
        }
        $response = $response.Utility::displayItem('run at user', $user)."\n";
        $daemonize = $conf->getConf("MAIN_SERVER.SETTING.daemonize");
        if($daemonize){
            $daemonize = 'true';
        }else{
            $daemonize = 'false';
        }
        $response = $response.Utility::displayItem('daemonize', $daemonize)."\n";
        $response = $response.Utility::displayItem('swoole version', phpversion('swoole'))."\n";
        $response = $response.Utility::displayItem('php version', phpversion())."\n";
        $response = $response.Utility::displayItem('easy swoole', SysConst::EASYSWOOLE_VERSION)."\n";
        $response = $response.Utility::displayItem('develop/produce', $mode)."\n";
        $response = $response.Utility::displayItem('temp dir', EASYSWOOLE_TEMP_DIR)."\n";
        $response = $response.Utility::displayItem('log dir', EASYSWOOLE_LOG_DIR)."\n";
        echo $response;
        Core::getInstance()->start();
        return null;
    }

    public function help(array $args): ?string
    {
        // TODO: Implement help() method.
        $logo = Utility::easySwooleLog();
        return $logo.<<<HELP_START
\e[33mOperation:\e[0m
\e[31m  php easyswoole start [arg1] [arg2]\e[0m
\e[33mIntro:\e[0m
\e[36m  to start current easyswoole server \e[0m
\e[33mArg:\e[0m
\e[32m  daemonize \e[0m                   run in daemonize
\e[32m  produce \e[0m                     load produce.php
HELP_START;
    }
}