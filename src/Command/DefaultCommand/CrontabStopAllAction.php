<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Definition\Option;
use EasySwoole\Command\Input\Input;
use EasySwoole\Bridge\StatusEnum;

class CrontabStopAllAction extends CrontabAction
{
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('stopAll')
            ->description('stop all crontab task')
            ->option(Option::string('mode', 'run mode, such as --mode=dev'))
            ->metadata('runtime', true);
    }
    protected function handle(Input $input): CommandResult
    {
        $result = new CommandResult();
        $package = $this->runtime->call('stopAllCrontabRule');
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
