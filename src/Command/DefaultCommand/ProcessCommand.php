<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\EasySwoole\Command\FrameworkCommand;

/** 进程管理命令，按定义分发操作。 */
final class ProcessCommand extends FrameworkCommand
{
    protected const NAME = 'process';
    protected const DESCRIPTION = 'EasySwoole process manager';
    protected const ACTIONS = [
        'show' => ProcessShowAction::class,
        'kill' => ProcessKillAction::class,
    ];
}
