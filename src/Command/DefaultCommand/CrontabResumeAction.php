<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Definition\Option;

class CrontabResumeAction extends CrontabTaskAction
{
    protected function action(): string { return 'resume'; }
    protected function bridgeAction(): string { return 'resumeCrontabRule'; }
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('resume')
            ->description('resume crontab tasks')
            ->option(Option::string('mode', 'run mode, such as --mode=dev'))
            ->option(Option::string('taskName', 'task name; omit to select interactively')->validate(
                static fn ($v) => $v === null || trim($v) !== '' ? true : 'taskName cannot be empty'))
            ->metadata('runtime', true);
    }
}
