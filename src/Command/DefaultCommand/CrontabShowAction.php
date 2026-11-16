<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Definition\Option;
use EasySwoole\Command\Input\Input;

class CrontabShowAction extends CrontabAction
{
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('show')
            ->description('show crontab task info')
            ->option(Option::string('mode', 'run mode, such as --mode=dev'))
            ->option(Option::number('watch', 'refresh interval in seconds', implicit: 2)->validate(static fn($v) => $v === null || $v > 0 ? true : 'watch must be positive'))
            ->metadata('runtime', true)
            ->metadata('watch', true);
    }
    protected function handle(Input $input): CommandResult
    {
        $result = new CommandResult();
        $tasks = $this->fetchTasks($result);
        return $tasks === null ? $result : CommandResult::success($this->formatTaskList($tasks), $tasks);
    }
}
