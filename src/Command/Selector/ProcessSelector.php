<?php

namespace EasySwoole\EasySwoole\Command\Selector;

use EasySwoole\EasySwoole\Command\Utility;

class ProcessSelector extends AbstractSelector
{
    protected function title(): string { return 'Select processes to terminate:'; }

    public static function details(array $choice): string
    {
        $memory = Utility::formatMemory($choice['memoryUsage'] ?? null);
        $started = Utility::formatTime($choice['startUpTime'] ?? null);
        return "Memory {$memory}   processUpTime {$started}";
    }

    public static function label(array $choice): string
    {
        // Do not interpret control characters from a configured process name as terminal escapes.
        $name = preg_replace('/[\x00-\x1f\x7f]/', '?', $choice['name']);
        return "{$name}   PID {$choice['pid']}";
    }
}
