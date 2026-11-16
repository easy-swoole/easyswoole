<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\EasySwoole\Command\FrameworkCommand;

/** 服务管理命令，按定义分发操作。 */
final class ServerCommand extends FrameworkCommand
{
    protected const NAME = 'server';
    protected const DESCRIPTION = 'EasySwoole server manager';
    protected const ACTIONS = [
        'start' => ServerStartAction::class,
        'stop' => ServerStopAction::class,
        'reload' => ServerReloadAction::class,
        'status' => ServerStatusAction::class,
    ];
}
