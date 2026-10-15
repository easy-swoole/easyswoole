<?php

namespace EasySwoole\EasySwoole\Command\Selector;

class CrontabSelector extends ProcessSelector
{
    public function __construct(private string $verb) {}

    protected function title(): string
    {
        return "Select crontab tasks to {$this->verb}:";
    }

    public static function label(array $task): string
    {
        return preg_replace('/[\x00-\x1f\x7f]/', '?', $task['taskName']);
    }

    public static function details(array $task): string
    {
        $rule = preg_replace('/[\x00-\x1f\x7f]/', '?', $task['taskRule'] ?? '-');
        $state = empty($task['isStop']) ? 'running' : 'stopped';
        return "Rule {$rule}   State {$state}   Next " . self::formatTime($task['taskNextRunTime'] ?? null);
    }
}
