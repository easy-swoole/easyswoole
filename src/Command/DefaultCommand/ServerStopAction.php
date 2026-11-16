<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Definition\Option;
use EasySwoole\Command\Input\Input;
use EasySwoole\EasySwoole\Config;

class ServerStopAction extends ServerAction
{
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('stop')
            ->description('stop EasySwoole server')
            ->option(Option::string('mode', 'run mode, such as --mode=dev'))
            ->option(Option::flag('force', 'stop with SIGKILL'))
            ->metadata('runtime', true);
    }
    public function __construct(private ?\EasySwoole\EasySwoole\Command\ServerControlService $server = null)
    { $this->server ??= new \EasySwoole\EasySwoole\Command\ServerControlService(); }

    protected function handle(Input $input): CommandResult
    {
        $pidFile = Config::getInstance()->getConf('MAIN_SERVER.SETTING.pid_file');
        if (!is_string($pidFile) || $pidFile === '') { return CommandResult::failure('server PID file is not configured'); }
        $pid = $this->server->stop($pidFile, $input->option('force'));
        return CommandResult::success("server stop for pid {$pid} at " . date('Y-m-d H:i:s'));
    }
}
