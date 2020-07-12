<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:57
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\AbstractCommand;

class Restart extends AbstractCommand
{
    public function commandName(): string
    {
        return 'restart';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addOpt('-d', '停止easywoole,并以守护进程模式启动');
        return $commandHelp;
    }

    public function exec(): string
    {
        echo (new Stop())->exec($args)->getMsg() . "\n";
        (new Start())->exec($args);
        return 'success';
    }
}
