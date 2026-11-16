<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Definition\Option;
use EasySwoole\Command\Input\Input;
use EasySwoole\EasySwoole\Config;

class ServerReloadAction extends ServerAction
{
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('reload')
            ->description('reload EasySwoole server')
            ->option(Option::string('mode', 'run mode, such as --mode=dev'))
            ->metadata('runtime', true);
    }
    public function __construct(private ?\EasySwoole\EasySwoole\Command\ServerControlService $server = null)
    { $this->server ??= new \EasySwoole\EasySwoole\Command\ServerControlService(); }

    protected function handle(Input $input): CommandResult
    {
        $pidFile = Config::getInstance()->getConf('MAIN_SERVER.SETTING.pid_file');
        if (!is_string($pidFile) || $pidFile === '') { return CommandResult::failure('server PID file is not configured'); }
        $pid = $this->server->reload($pidFile);
        return CommandResult::success("send server reload command to pid:{$pid} at " . date('Y-m-d H:i:s'));
    }
}
