<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2020/4/7 0007
 * Time: 15:47
 */

namespace EasySwoole\EasySwoole\Bridge\DefaultCommand;


use EasySwoole\Component\Process\Manager;
use EasySwoole\EasySwoole\Bridge\BridgeCommand;

class Process extends Base
{
    static function initCommand(BridgeCommand $command)
    {
        $command->set(BridgeCommand::PROCESS_INFO, [Process::class,'info']);
    }

    static function info(){
        return Manager::getInstance()->info();
    }
}
