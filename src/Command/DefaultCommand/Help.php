<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-01-24
 * Time: 23:15
 */

namespace EasySwoole\EasySwoole\Command\DefaultCommand;


use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Result;
use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\CommandRunner;
use EasySwoole\EasySwoole\Command\Utility;

class Help implements CommandInterface
{
    public function commandName(): string
    {
        return 'help';
    }

    public function exec($args): ResultInterface
    {
        if (!isset($args[0])) {
            return $this->help($args);
        } else {
            $actionName = $args[0];
            array_shift($args);
            $call = CommandRunner::getInstance()->commandContainer()->get($actionName);
            if ($call instanceof CommandInterface) {
                return $call->help($args);
            } else {
                $ret = new Result();
                $ret->setMsg("no help message for command {$actionName} was found");
                return $ret;
            }
        }
    }

    public function help($args): ResultInterface
    {
        $allCommand = implode(PHP_EOL, array_keys(CommandRunner::getInstance()->commandContainer()->all()));
        $msg = Utility::easySwooleLog().<<<HELP
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