<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Definition\Option;
use EasySwoole\Command\Input\Input;
use EasySwoole\EasySwoole\Command\FrameworkAction;

class ProcessShowAction extends FrameworkAction
{
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('show')
            ->description('show EasySwoole process list')
            ->option(Option::string('mode', 'run mode, such as --mode=dev'))
            ->option(Option::number('watch', 'refresh interval in seconds', implicit: 2)->validate(static fn($v) => $v === null || $v > 0 ? true : 'watch must be positive'))
            ->metadata('runtime', true)
            ->metadata('watch', true);
    }
    public function __construct(private ?\EasySwoole\EasySwoole\Command\ProcessControlService $processes = null)
    { $this->processes ??= new \EasySwoole\EasySwoole\Command\ProcessControlService(); }

    protected function handle(Input $input): CommandResult
    {
        return $this->processes->listing();
    }
}
