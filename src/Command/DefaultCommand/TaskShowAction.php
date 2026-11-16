<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Definition\Option;
use EasySwoole\Command\Input\Input;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Bridge\StatusEnum;

class TaskShowAction extends TaskAction
{
    public static function createDefinition(): ActionDefinition
    {
        return ActionDefinition::create('show')
            ->description('show custom Task worker status')
            ->option(Option::string('mode', 'run mode, such as --mode=dev'))
            ->option(Option::number('watch', 'refresh interval in seconds', implicit: 2)->validate(static fn($v) => $v === null || $v > 0 ? true : 'watch must be positive'))
            ->metadata('runtime', true)
            ->metadata('watch', true);
    }
    protected function handle(Input $input): CommandResult
    {
        $result = new CommandResult();
        $package = $this->requestStatus();
        if ($package->getStatus() !== StatusEnum::SUCCESS) {
            return CommandResult::failure($package->getMsg() ?? 'cannot query Task status');
        }
        $rows = $package->getArgs();
        if (!is_array($rows)) {
            throw new \UnexpectedValueException('invalid Task status');
        }
        foreach ($rows as $row) {
            foreach (['pid', 'accepting', 'running', 'success', 'fail'] as $field) {
                if (!is_array($row) || !isset($row[$field]) || !is_int($row[$field]) || $row[$field] < 0) {
                    throw new \UnexpectedValueException('invalid Task status');
                }
            }
            if ($row['pid'] === 0 || !in_array($row['accepting'], [0, 1], true)) {
                throw new \UnexpectedValueException('invalid Task status');
            }
        }
        $result->data = $rows;
        $lines = ['Task workers (total: ' . count($rows) . ')'];
        if (!$rows) {
            $lines[] = '  No Task workers available.';
        }
        foreach ($rows as $index => $row) {
            $state = empty($row['accepting']) ? 'draining' : 'accepting';
            $lines[] = '';
            $lines[] = "[{$index}] PID {$row['pid']}   State {$state}";
            $lines[] = "    Running {$row['running']}   Success {$row['success']}   Fail {$row['fail']}";
            $lines[] = '    processUpTime ' . Utility::formatTime($row['startUpTime'] ?? null);
        }
        $result->message = implode(PHP_EOL, $lines);
        return $result;
    }
}
