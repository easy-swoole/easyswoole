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
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\SysConst;

class Start extends AbstractCommand
{

    public function commandName(): string
    {
        return 'start';
    }

    public function help(): array
    {
        return [
            '',
            '[d]',
            '[produce]',
            '[produce] [d]'
        ];
    }

    public function desc(): string
    {
        return '启动EasySwoole';
    }

    public function exec(): string
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
            default:
            {
                $serverType = 'UNKNOWN';
            }
        }
        $displayItem['main server'] = $serverType;
        $displayItem['listen address'] = $conf->getConf('MAIN_SERVER.LISTEN_ADDRESS');
        $displayItem['listen port'] = $conf->getConf('MAIN_SERVER.PORT');
        $data = $conf->getConf('MAIN_SERVER.SETTING');
        if (empty($data['user'])) {
            $data['user'] = get_current_user();
        }
        $displayItem = $displayItem + $data;
        $displayItem['swoole version'] = phpversion('swoole');
        $displayItem['php version'] = phpversion();
        $displayItem['easyswoole version'] = SysConst::EASYSWOOLE_VERSION;
        $displayItem['develop/produce'] = Core::getInstance()->runMode() ? 'dev' : 'produce';
        $displayItem['temp dir'] = EASYSWOOLE_TEMP_DIR;
        $displayItem['log dir'] = EASYSWOOLE_LOG_DIR;
        foreach ($displayItem as $key => $value) {
            $msg .= Utility::displayItem($key, $value) . "\n";
        }
        echo $msg;
        Core::getInstance()->initialize()->globalInitialize()->createServer()->start();
    }
}