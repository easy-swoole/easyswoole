<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Definition\Option;
use EasySwoole\Command\Input\Input;
use EasySwoole\Bridge\StatusEnum;

class ServerStatusAction extends ServerAction
{
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('status')
            ->description('get EasySwoole server status')
            ->option(Option::string('mode', 'run mode, such as --mode=dev'))
            ->option(Option::number('watch', 'refresh interval in seconds', implicit: 2)->validate(static fn($v) => $v === null || $v > 0 ? true : 'watch must be positive'))
            ->metadata('runtime', true)
            ->metadata('watch', true);
    }
    protected function handle(Input $input): CommandResult
    {
        $package = $this->requestServerStatus();
        if ($package->getStatus() !== StatusEnum::SUCCESS) {
            return CommandResult::failure($package->getMsg() ?? 'cannot query server status');
        }
        $data = $package->getArgs();
        if (!is_array($data) || !isset($data['env'], $data['runtime']) || !is_array($data['env']) || !is_array($data['runtime'])) {
            throw new \UnexpectedValueException('invalid server status returned by Bridge');
        }
        return CommandResult::success($this->formatServerStatus($data), $data);
    }
}
