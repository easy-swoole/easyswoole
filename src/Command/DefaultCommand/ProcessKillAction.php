<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Definition\Option;
use EasySwoole\Command\Input\Input;
use EasySwoole\EasySwoole\Command\FrameworkAction;

class ProcessKillAction extends FrameworkAction
{
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('kill')
            ->description('kill EasySwoole process')
            ->option(Option::string('mode', 'run mode, such as --mode=dev'))
            ->option(Option::integer('pid', 'kill specified pid')->validate(static fn($v) => $v === null || $v > 0 ? true : 'pid must be a positive integer'))
            ->option(Option::string('group', 'match process group')->validate(static fn($v) => $v === null || trim($v) !== '' ? true : 'group cannot be empty'))
            ->option(Option::string('name', 'match process name')->validate(static fn($v) => $v === null || trim($v) !== '' ? true : 'name cannot be empty'))
            ->option(Option::flag('force', 'use SIGKILL; default SIGTERM'))
            ->metadata('runtime', true);
    }
    public function __construct(private ?\EasySwoole\EasySwoole\Command\ProcessControlService $processes = null)
    { $this->processes ??= new \EasySwoole\EasySwoole\Command\ProcessControlService(); }

    protected function handle(Input $input): CommandResult
    {
        $selection = new \EasySwoole\EasySwoole\Command\ProcessSelection(
            pid: $input->option('pid'),
            group: $input->option('group'),
            name: $input->option('name'),
            force: $input->option('force'),
        );
        return $this->processes->terminate($selection, $this->console);
    }
}
