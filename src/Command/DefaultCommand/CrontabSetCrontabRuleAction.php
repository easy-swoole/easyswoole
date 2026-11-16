<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Definition\Option;
use EasySwoole\Command\Input\Input;
use EasySwoole\Bridge\StatusEnum;

class CrontabSetCrontabRuleAction extends CrontabAction
{
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('setCrontabRule')
            ->description('update an crontab task time rule')
            ->option(Option::string('mode', 'run mode, such as --mode=dev'))
            ->option(Option::string('taskName', 'crontab task name', required: true)->validate(static fn($v) => trim($v) !== '' ? true : 'taskName must be set'))
            ->option(Option::string('taskRule', 'crontab time rule', required: true)->validate(static fn($v) => trim($v) !== '' ? true : 'taskRule must be set'))
            ->metadata('runtime', true);
    }
    protected function handle(Input $input): CommandResult
    {
        $result = new CommandResult();
        $package = $this->runtime->call('setCrontabRule', ['taskName' => $input->option('taskName'), 'taskRule' => $input->option('taskRule')]);
        if ($package->getStatus() == StatusEnum::SUCCESS) {
            $result->data = $package->getMsg();
            $result->message = $package->getMsg();
        } else {
            $result->message = $package->getMsg();
            $result->exitCode = 1;
        }
        return $result;
    }
}
