<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2020/4/7 0007
 * Time: 15:47
 */

namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\EasySwoole\Bridge\BridgeCommand;
use EasySwoole\EasySwoole\Bridge\Package;
use EasySwoole\EasySwoole\ServerManager;

class Server extends Base
{
    static function initCommand(BridgeCommand $command)
    {
        $command->set(BridgeCommand::SERVER_STATUS_INFO,[Server::class,'statusInfo']);

    }
    static function statusInfo(Package $package,Package $response){
        $response->setArgs(ServerManager::getInstance()->getSwooleServer()->stats());
        return true;
    }
}
