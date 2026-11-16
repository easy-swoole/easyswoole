<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\EasySwoole\Command\FrameworkCommand;

/** 定时任务管理命令，按定义分发操作。 */
final class CrontabCommand extends FrameworkCommand
{
    protected const NAME = 'crontab';
    protected const DESCRIPTION = 'EasySwoole crontab manager';
    protected const ACTIONS = [
        'show' => CrontabShowAction::class,
        'stop' => CrontabStopAction::class,
        'stopAll' => CrontabStopAllAction::class,
        'resume' => CrontabResumeAction::class,
        'resumeAll' => CrontabResumeAllAction::class,
        'runJobNow' => CrontabRunJobNowAction::class,
        'setCrontabRule' => CrontabSetCrontabRuleAction::class,
        'reload' => CrontabReloadAction::class,
    ];
}
