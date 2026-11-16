<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Definition\Option;

class CrontabRunJobNowAction extends CrontabTaskAction
{
    protected function action(): string { return 'runJobNow'; }
    protected function bridgeAction(): string { return 'runCrontabJobNow'; }
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('runJobNow')
            ->description('runJobNow crontab tasks')
            ->option(Option::string('mode', 'run mode, such as --mode=dev'))
            ->option(Option::string('taskName', 'task name; omit to select interactively')->validate(
                static fn ($v) => $v === null || trim($v) !== '' ? true : 'taskName cannot be empty'))
            ->metadata('runtime', true);
    }
}
