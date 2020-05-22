<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:44
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
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
        return 'start';
    }

    public function exec($args): ResultInterface
    {
        $result = new Result();
        $msg = '';
        $conf = Config::getInstance();
        $serverType = $conf->getConf('MAIN_SERVER.SERVER_TYPE');
        $displayItem = [];
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
        $displayItem['main server'] = $serverType;
        $displayItem['listen address'] = $conf->getConf('MAIN_SERVER.LISTEN_ADDRESS');
        $displayItem['listen port'] = $conf->getConf('MAIN_SERVER.PORT');
        $data = $conf->getConf('MAIN_SERVER.SETTING');
        if(empty($data['user'])){
            $data['user'] = get_current_user();
        }
        $displayItem = $displayItem + $data;
        foreach ($displayItem as $key => $value){
            $msg .= Utility::displayItem($key,$value)."\n";
        }
        $result->setMsg($msg);
        return $result;
    }

    public function help($args): ResultInterface
    {
        // TODO: Implement help() method.
    }

}