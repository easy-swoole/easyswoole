<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Definition\Option;
use EasySwoole\Command\Input\Input;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;

class ServerStartAction extends ServerAction
{
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('start')
            ->description('start EasySwoole server')
            ->option(Option::string('mode', 'run mode, such as --mode=dev'))
            ->option(Option::flag('d', 'start with daemonize mode'))
            ->metadata('runtime', true);
    }
    protected function handle(Input $input): CommandResult
    {
        $result = new CommandResult();
        defined('EASYSWOOLE_RUNNING') or define('EASYSWOOLE_RUNNING', true);
        $conf = Config::getInstance();
        $daemonize = $input->option('d');
        if ($daemonize) {
            $conf->setConf("MAIN_SERVER.SETTING.daemonize", $daemonize);
        }
        if (empty($conf->getConf('MAIN_SERVER.SETTING.user'))) {
            $conf->setConf('MAIN_SERVER.SETTING.user', get_current_user());
        }
        $displayItem = Utility::generateEnv(Config::getInstance(), Core::getInstance()->runMode());
        $msg = Utility::easySwooleLog() . "\nEnvironment\n";
        $msg .= $this->formatStatusSection($displayItem, true) . "\n";
        $this->console->line(rtrim($msg));
        Core::getInstance()->createServer()->start();
        return $result;
    }
}
