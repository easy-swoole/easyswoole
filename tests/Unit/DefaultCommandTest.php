<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Command\Application;
use EasySwoole\Command\Contracts\CommandInterface;
use EasySwoole\Command\Input\InputParser;
use EasySwoole\Command\Input\InputBinder;
use EasySwoole\EasySwoole\Command\DefaultCommandProvider;
use EasySwoole\EasySwoole\Command\CommandRunner;
use PHPUnit\Framework\TestCase;

final class DefaultCommandTest extends TestCase
{
    public function testPublicCommandPathsArePreserved(): void
    {
        $app = new Application(); $app->addProvider(new DefaultCommandProvider());
        $expected = ['install', 'server start', 'server stop', 'server reload', 'server status',
            'process show', 'process kill', 'task show', 'task reload', 'crontab show', 'crontab stop',
            'crontab stopAll', 'crontab resume', 'crontab resumeAll', 'crontab runJobNow',
            'crontab setCrontabRule', 'crontab reload'];
        $actual = [];
        foreach ($app->registry()->definitions() as $definition) {
            if (!$definition->hasActions()) { $actual[] = $definition->name; }
            foreach ($definition->actions() as $action) { $actual[] = $definition->name . ' ' . $action->name; }
        }
        sort($expected); sort($actual);
        self::assertSame($expected, $actual);
        foreach (array_keys($app->registry()->definitions()) as $name) {
            self::assertInstanceOf(CommandInterface::class, $app->registry()->create($name));
            self::assertNotSame('', $app->registry()->definitions()[$name]->summary());
            foreach ($app->registry()->definitions()[$name]->actions() as $action) {
                self::assertNotSame('', $action->summary());
            }
        }
    }

    public function testHelpAndInvalidInputDoNotInitializeFramework(): void
    {
        $app = new class extends CommandRunner {
            public int $initializations = 0;
            protected function initializeRuntime(?string $mode): void { ++$this->initializations; }
        };
        foreach ([[], ['server'], ['server', 'start', '--help'], ['help', 'process', 'kill']] as $argv) {
            self::assertSame(0, $app->dispatch($argv)->exitCode);
        }
        foreach ([['process', 'kill', '--pid=bad'], ['server', 'start', '--mode'],
            ['crontab', 'setCrontabRule'], ['task', 'reload', '--timeout=0'], ['server', 'typo']] as $argv) {
            self::assertSame(2, $app->dispatch($argv)->exitCode);
        }
        foreach ([[], ['--rolling=false']] as $options) {
            $result = $app->dispatch(['task', 'reload', '--timeout=10', ...$options]);
            self::assertSame(2, $result->exitCode);
            self::assertStringContainsString('--timeout requires --rolling', $result->message);
        }
        self::assertSame(0, $app->initializations);
    }

    public function testExistingOptionSpellingsAndValuesBind(): void
    {
        $app = new Application();
        $app->addProvider(new DefaultCommandProvider());
        $parser = new InputParser();
        $binder = new InputBinder();
        foreach ([
            [['server', 'start', '-d', '--mode=produce'], 'd', true],
            [['server', 'stop', '-force'], 'force', true],
            [['process', 'kill', '--pid=9501', '--force=false'], 'pid', 9501],
            [['task', 'reload', '--rolling', '--timeout=5'], 'rolling', true],
            [['task', 'reload', '--rolling=true', '--timeout=5'], 'timeout', 5.0],
            [['crontab', 'setCrontabRule', '--taskName=demo', '--taskRule=* * * * *'], 'taskRule', '* * * * *'],
            [['server', 'status', '--watch'], 'watch', 2.0],
        ] as [$argv, $key, $expected]) {
            $parsed = $parser->parse($argv, $app->registry());
            self::assertSame($expected, $binder->bind($parsed->action ?? $parsed->command, $parsed->options, $parsed->words)->option($key));
        }
    }

    public function testHelpUsesCommandAndActionHierarchyWithoutInitializingRuntime(): void
    {
        $app = new class extends CommandRunner {
            public int $initializations = 0;
            protected function initializeRuntime(?string $mode): void { ++$this->initializations; }
        };
        $root = $app->dispatch([])->message;
        self::assertStringContainsString('current support command is:', $root);
        foreach (['install', 'server', 'process', 'crontab', 'task', 'EasySwoole server manager'] as $text) {
            self::assertStringContainsString($text, $root);
        }
        self::assertStringNotContainsString('--mode', $root);
        $server = $app->dispatch(['server'])->message;
        self::assertStringContainsString('current support action is:', $server);
        foreach (['start', 'stop', 'reload', 'status', '--d', '--mode', '--force'] as $text) {
            self::assertStringContainsString($text, $server);
        }
        $start = $app->dispatch(['server', 'start', '--help'])->message;
        self::assertStringContainsString('--d', $start);
        self::assertStringNotContainsString('--force', $start);
        self::assertSame(0, $app->initializations);
    }

    public function testWrongOperationHandlerCannotBeInjectedIntoAnotherAction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new \EasySwoole\EasySwoole\Command\DefaultCommand\ServerCommand([
            'start' => new \EasySwoole\EasySwoole\Command\DefaultCommand\ServerStopAction(),
        ]);
    }
}
