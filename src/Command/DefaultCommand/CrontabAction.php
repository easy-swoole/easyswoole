<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Console\Selection\Choice;
use EasySwoole\EasySwoole\Command\FrameworkAction;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\EasySwoole\Command\Selector\CrontabSelector;

abstract class CrontabAction extends FrameworkAction
{
    protected function formatTaskList(array $tasks): string
    {
        if (!$tasks) {
            return 'No crontab tasks registered.';
        }
        $stopped = count(array_filter($tasks, static fn(array $task) => !empty($task['isStop'])));
        $running = count($tasks) - $stopped;
        $lines = ['Crontab tasks (total: ' . count($tasks) . ", running: {$running}, stopped: {$stopped})"];
        $index = 0;
        foreach ($tasks as $name => $task) {
            $task['taskName'] = (string) $name;
            $lines[] = '';
            $lines[] = '[' . ++$index . '] ' . CrontabSelector::label($task);
            $lines[] = '    ' . CrontabSelector::details($task);
            $runs = $task['taskRunTimes'] ?? 0;
            $lastRun = Utility::formatTime($task['taskLastRunTime'] ?? null);
            $lines[] = "    Runs {$runs}   Last run {$lastRun}";
            $error = $task['lastError'] ?? '';
            $errorTime = Utility::formatTime($task['lastErrorTime'] ?? null);
            if ($error !== '' || $errorTime !== '-') {
                $error = preg_replace('/[\x00-\x1f\x7f]/', '?', $error);
                $lines[] = '    Last error ' . ($error === '' ? '-' : $error);
                $lines[] = '    Last error time ' . $errorTime;
            }
        }
        return implode(PHP_EOL, $lines);
    }
    protected function fetchTasks(CommandResult $result): ?array
    {
        $package = $this->callBridge('crontabInfo');
        if ($package->getStatus() !== StatusEnum::SUCCESS) {
            $result->exitCode = 1;
            $result->message = $package->getMsg();
            return null;
        }
        if (!is_array($package->getArgs())) {
            throw new \UnexpectedValueException('invalid crontab task list returned by Bridge');
        }
        return $package->getArgs();
    }
    protected function callBridge(string $action, array $args = []): Package
    {
        return $this->runtime->call($action, $args);
    }
    protected function selectTasks(array $choices, string $verb): array
    {
        $items = array_map(static fn($row) => new Choice($row['taskName'], CrontabSelector::label($row), $row, CrontabSelector::details($row)), $choices);
        return $this->console->select("Select crontab tasks to {$verb}:", $items, multiple: true)->values();
    }
}
