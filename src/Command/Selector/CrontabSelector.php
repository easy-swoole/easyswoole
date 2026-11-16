<?php

namespace EasySwoole\EasySwoole\Command\Selector;

use EasySwoole\EasySwoole\Command\Utility;

class CrontabSelector
{


    public static function label(array $choice): string
    {
        return preg_replace('/[\x00-\x1f\x7f]/', '?', $choice['taskName']);
    }

    public static function details(array $choice): string
    {
        $rule = preg_replace('/[\x00-\x1f\x7f]/', '?', $choice['taskRule'] ?? '-');
        $state = empty($choice['isStop']) ? 'running' : 'stopped';
        return "Rule {$rule}   State {$state}   Next " . Utility::formatTime($choice['taskNextRunTime'] ?? null);
    }
}
