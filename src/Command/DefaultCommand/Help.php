<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:15
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\CallerInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\EasySwoole\Command\CommandRunner;
use EasySwoole\EasySwoole\Command\Utility;

class Help extends AbstractCommand
{
    public function commandName(): string
    {
        return 'help';
    }

    public function exec(CallerInterface $caller): ResultInterface
    {
        if (!$caller->getParams()) {
            return $this->help($caller);
        }
        $actionName = key($caller->getOneParam());
        $call = CommandRunner::getInstance()->commandContainer()->get($actionName);

        if (!$call instanceof CommandInterface) {
            $ret = new Result();
            $ret->setMsg("no help message for command {$actionName} was found");
            return $ret;
        }

        return $call->help($caller);
    }

    public function help(CallerInterface $caller): ResultInterface
    {
        $allCommand = implode(PHP_EOL, array_keys(CommandRunner::getInstance()->commandContainer()->all()));
        $msg = Utility::easySwooleLog() . <<<HELP
Welcome To EasySwoole Command Console!
Usage: php easyswoole [command] [arg]
Get help : php easyswoole help [command]
Current Register Command:
{$allCommand}
HELP;
        $ret = new Result();
        $ret->setMsg($msg);
        return $ret;
    }
}