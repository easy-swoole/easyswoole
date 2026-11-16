<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Command;

use EasySwoole\Command\CommandRegistry;
use EasySwoole\Command\Contracts\CommandProviderInterface;
use EasySwoole\EasySwoole\Command\DefaultCommand;

final class DefaultCommandProvider implements CommandProviderInterface
{
    public function register(CommandRegistry $registry): void
    {
        foreach ([DefaultCommand\InstallCommand::class, DefaultCommand\ServerCommand::class,
            DefaultCommand\ProcessCommand::class, DefaultCommand\CrontabCommand::class,
            DefaultCommand\TaskCommand::class] as $class) {
            $registry->register($class::createDefinition(), static fn () => new $class());
        }
    }
}
