<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\EasySwoole\Command\DefaultCommand\Install;
use EasySwoole\EasySwoole\Command\DefaultCommand\Server;
use EasySwoole\EasySwoole\Command\DefaultCommand\Process;
use EasySwoole\EasySwoole\Command\DefaultCommand\Crontab;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DefaultCommandTest extends TestCase
{
    public static function commands(): iterable
    {
        yield [Server::class, 'server', ['start', 'stop', 'reload', 'status']];
        yield [Process::class, 'process', ['show', 'kill']];
        yield [Crontab::class, 'crontab', ['show', 'stop', 'stopAll', 'resume', 'resumeAll', 'runJobNow', 'setCrontabRule']];
    }

    #[DataProvider('commands')]
    public function testCommandsRegisterExpectedActions(string $class, string $name, array $actions): void
    {
        $command = new $class();
        self::assertSame($name, $command->name());
        self::assertNotSame('', $command->description());
        self::assertSame($actions, array_keys($command->getActions()));
    }

    public function testInstallRegistersDefaultActionWithoutExecutingIt(): void
    {
        $command = new Install();
        self::assertSame('install', $command->name());
        self::assertSame('default', $command->getDefaultAction()->name);
        self::assertSame([], $command->getActions());
    }
}
