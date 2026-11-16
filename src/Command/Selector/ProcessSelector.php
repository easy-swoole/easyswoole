<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\Selector;

use EasySwoole\EasySwoole\Command\Utility;

/** 进程列表与通用终端选择器共用的业务标签。 */
final class ProcessSelector
{
    public static function details(array $choice): string
    {
        $memory = Utility::formatMemory($choice['memoryUsage'] ?? null);
        $started = Utility::formatTime($choice['startUpTime'] ?? null);
        return "Memory {$memory}   processUpTime {$started}";
    }

    public static function label(array $choice): string
    {
        $name = preg_replace('/[\x00-\x1f\x7f]/', '?', $choice['name']);
        return "{$name}   PID {$choice['pid']}";
    }
}
