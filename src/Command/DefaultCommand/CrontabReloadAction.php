<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Definition\Option;
use EasySwoole\Command\Input\Input;
use EasySwoole\EasySwoole\Command\FrameworkAction;

use EasySwoole\EasySwoole\Command\ProcessControlService;
use EasySwoole\EasySwoole\Crontab\Crontab as CrontabManager;

class CrontabReloadAction extends FrameworkAction
{
    public function __construct(private ?ProcessControlService $processes = null)
    { $this->processes ??= new ProcessControlService(); }
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('reload')
            ->description('reload crontab processes; default SIGTERM')
            ->option(Option::string('mode', 'run mode, such as --mode=dev'))
            ->option(Option::flag('force', 'use SIGKILL; default SIGTERM'))
            ->metadata('runtime', true);
    }
    protected function handle(Input $input): CommandResult
    {
        $group = $this->processGroup();
        $selection = new \EasySwoole\EasySwoole\Command\ProcessSelection(group: $group, force: $input->option('force'));
        $result = $this->processes->terminate($selection, $this->console);
        if ($result->exitCode === 0 && $result->data === []) {
            return CommandResult::failure("No crontab processes found in group {$group}.", data: []);
        }
        return $result;
    }
    protected function processGroup(): string
    {
        return CrontabManager::getInstance()->getConfig()->getServerName() . '.Crontab';
    }
}
