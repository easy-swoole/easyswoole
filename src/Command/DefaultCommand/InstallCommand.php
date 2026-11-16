<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command\DefaultCommand;

use EasySwoole\EasySwoole\Command\FrameworkCommand;

/** 框架安装命令，按定义分发操作。 */
final class InstallCommand extends FrameworkCommand
{
    protected const NAME = 'install';
    protected const DESCRIPTION = 'EasySwoole framework installation';
    protected const STANDALONE = InstallAction::class;
}
