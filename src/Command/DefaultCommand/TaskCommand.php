<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\EasySwoole\Command\FrameworkCommand;

/** 任务工作进程管理命令，按定义分发操作。 */
final class TaskCommand extends FrameworkCommand
{
    protected const NAME = 'task';
    protected const DESCRIPTION = 'EasySwoole Task worker manager';
    protected const ACTIONS = [
        'show' => TaskShowAction::class,
        'reload' => TaskReloadAction::class,
    ];
}
