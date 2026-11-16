<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Command\Console\Selection\Choice;
use EasySwoole\Command\Definition\Argument;
use EasySwoole\Command\Event\ExceptionEvent;
use EasySwoole\Command\Validation\Rules;
use EasySwoole\Command\Definition\CommandDefinition;
use EasySwoole\Command\Definition\ActionDefinition;
use EasySwoole\Command\Contracts\CommandInterface;
use EasySwoole\Command\Contracts\CommandProviderInterface;
use EasySwoole\Command\CommandRegistry;
use EasySwoole\Command\Result\CommandResult;
use EasySwoole\Command\Console\Console;
use EasySwoole\Command\Input\Input;
use EasySwoole\Command\Definition\Option;
use EasySwoole\Command\Console\Terminal;
use EasySwoole\EasySwoole\Command\{CommandRunner, RuntimeClient, ServerControlService};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CommandIntegrationTest extends TestCase
{
    public function testExternalComponentCanRegisterWithoutFrameworkInitialization(): void
    {
        $dependency = new \stdClass();
        $runner = new class extends CommandRunner {
            public int $initializations = 0;
            protected function initializeRuntime(?string $mode): void { ++$this->initializations; }
        };
        $runner->addProvider(new class($dependency) implements CommandProviderInterface {
            public function __construct(private object $dependency) {}
            public function register(CommandRegistry $registry): void
            {
                $registry->add(new class($this->dependency) implements CommandInterface {
                    public function __construct(private object $dependency) {}
                    public function definition(): CommandDefinition { return CommandDefinition::create('component')->description('Component commands')->action(ActionDefinition::create('check')->option(Option::flag('enabled'))); }
                    public function execute(Input $input, Console $console): CommandResult { return CommandResult::success(data: [$this->dependency, $input->option('enabled')]); }
                });
            }
        });
        $result = $runner->dispatch(['component', 'check', '--enabled']);
        self::assertSame(0, $result->exitCode);
        self::assertSame([$dependency, true], $result->data);
        self::assertSame(0, $runner->initializations);
    }

    public function testComponentValidationAndExceptionObservationBeforeRuntimeInitialization(): void
    {
        $error = new \RuntimeException('component failure', 0, new \LogicException('original cause'));
        $runner = new class extends CommandRunner {
            public int $initializations = 0;
            protected function initializeRuntime(?string $mode): void { ++$this->initializations; }
        };
        $count = Argument::integer('count', required: true)->validate(Rules::range(1, 5));
        $action = ActionDefinition::create('run')->argument($count)->metadata('runtime', true);
        $definition = CommandDefinition::create('component')->action($action);
        $created = 0;
        $runner->registry()->register($definition, function () use (&$created): CommandInterface {
            ++$created;
            throw new \LogicException('factory must not run');
        });
        $events = [];
        $runner->onException(function (ExceptionEvent $event) use (&$events): void { $events[] = $event; });

        // 输入失败早于运行环境和业务工厂；观察器仍可记录原始输入异常。
        self::assertSame(2, $runner->dispatch(['component', 'run', '0'])->exitCode);
        self::assertSame('component', $events[0]->command);
        self::assertSame('run', $events[0]->action);
        self::assertNull($events[0]->input);
        self::assertSame(0, $runner->initializations);
        self::assertSame(0, $created);

        $runner->registry()->replace($definition, static fn () => throw $error);
        self::assertSame(1, $runner->dispatch(['component', 'run', '3'])->exitCode);
        self::assertSame($error, $events[1]->error);
        self::assertSame(3, $events[1]->input->argument('count'));
        self::assertSame(1, $runner->initializations);
    }

    public function testComponentWholeValidationRepeatedValuesAndArgumentSources(): void
    {
        $runner = new class extends CommandRunner {
            public int $initializations = 0;
            protected function initializeRuntime(?string $mode): void { ++$this->initializations; }
        };
        $ids = Option::integer('id')->multiple()->validate(Rules::range(1, 10));
        $action = ActionDefinition::create('run');
        $action->option($ids);
        $action->option(Option::flag('all'));
        $action->argument(Argument::string('destination', default: 'default'));
        $action->metadata('runtime', true);
        $action->validateInput(static function (Input $input): bool|string {
            return $input->option('all') && $input->provided('id') ? '--all 与 --id 不能同时使用' : true;
        });
        $definition = CommandDefinition::create('component')->action($action);
        $created = 0;
        $runner->registry()->register($definition, function () use ($definition, &$created): CommandInterface {
            ++$created;
            return new class($definition) implements CommandInterface {
                public function __construct(private CommandDefinition $definition) {}
                public function definition(): CommandDefinition { return $this->definition; }
                public function execute(Input $input, Console $console): CommandResult {
                    return CommandResult::success(data: [$input->option('id'), $input->arguments(), $input->argumentProvided('destination')]);
                }
            };
        });

        // 跨字段错误和帮助均不初始化框架，也不创建业务实例。
        self::assertSame(2, $runner->dispatch(['component', 'run', '--all', '--id=1'])->exitCode);
        self::assertSame(0, $runner->dispatch(['component', 'run', '--help'])->exitCode);
        self::assertSame(0, $runner->initializations);
        self::assertSame(0, $created);

        $result = $runner->dispatch(['component', 'run', '--id=01', '--id=2']);
        self::assertSame(0, $result->exitCode);
        self::assertSame([[1, 2], ['destination' => 'default'], false], $result->data);
        self::assertSame(1, $runner->initializations);
        self::assertSame(1, $created);
        $result = $runner->dispatch(['component', 'run', '--id=3', 'destination=default']);
        self::assertSame([[3], ['destination' => 'default'], true], $result->data);
    }

    public function testWatchRefreshesWithoutReinitializingAndRetainsLastData(): void
    {
        $runner = new class extends CommandRunner {
            public array $modes = [];
            protected function initializeRuntime(?string $mode): void { $this->modes[] = $mode; }
        };
        $command = new class implements CommandInterface {
            public int $frames = 0;
            public function definition(): CommandDefinition {
                return CommandDefinition::create('component')->description('Component commands')->action(ActionDefinition::create('status')
                    ->option(Option::string('mode'))->option(Option::number('watch', implicit: .01))
                    ->metadata('runtime', true)->metadata('watch', true));
            }
            public function execute(Input $input, Console $console): CommandResult {
                return ++$this->frames === 3 ? CommandResult::failure('Bridge offline', data: ['frame' => 3])
                    : CommandResult::success('snapshot', ['frame' => $this->frames]);
            }
        };
        $terminal = new class extends Terminal {
            public bool $closed = false;
            private float $time = 0;
            public function open(): void {}
            public function close(): void { $this->closed = true; }
            public function dimensions(): array { return [24, 80]; }
            public function frame(string $text): void {}
            public function now(): float { return $this->time; }
            public function wait(float $seconds): bool { $this->time += max(.01, $seconds); return false; }
        };
        $runner->addCommand($command);
        $result = $runner->dispatch(['component', 'status', '--mode=test', '--watch'], new Console(terminal: $terminal));
        self::assertSame(['test'], $runner->modes);
        self::assertSame(3, $command->frames);
        self::assertSame(1, $result->exitCode);
        self::assertSame('Bridge offline', $result->message);
        self::assertSame(['frame' => 3], $result->data);
        self::assertTrue($terminal->closed);
    }

    public function testRuntimeClientUsesExistingCoroutineAndPropagatesErrors(): void
    {
        $client = new RuntimeClient();
        $cid = $client->run(fn () => \Swoole\Coroutine::getCid());
        self::assertGreaterThanOrEqual(0, $cid);
        self::assertTrue($client->run(fn () => $client->run(fn () => \Swoole\Coroutine::getCid()) === \Swoole\Coroutine::getCid()));
        $this->expectExceptionMessage('callback failed');
        $client->run(static fn () => throw new \RuntimeException('callback failed'));
    }

    public function testServerControlRejectsMalformedPidsBeforeSignalling(): void
    {
        $service = new class extends ServerControlService {
            public array $signals = [];
            protected function signal(int $pid, int $signal): bool { $this->signals[] = [$pid, $signal]; return true; }
        };
        $file = tempnam(sys_get_temp_dir(), 'es-cli-');
        try {
            foreach (['0', '-1', '123oops', '999999999999999999999'] as $value) {
                file_put_contents($file, $value);
                try { $service->reload($file); self::fail('must reject malformed PID'); }
                catch (\RuntimeException $error) { self::assertSame('invalid server PID file', $error->getMessage()); }
            }
            self::assertSame([], $service->signals);
        } finally { unlink($file); }
    }

    public function testServerControlSendsExpectedSignalsAndCleansPidFile(): void
    {
        $service = new class extends ServerControlService {
            public array $signals = [];
            private int $checks = 0;
            protected function signal(int $pid, int $signal): bool {
                $this->signals[] = [$pid, $signal];
                return $signal !== 0 || ++$this->checks < 2;
            }
            protected function signalError(): int { return 3; }
        };
        $file = tempnam(sys_get_temp_dir(), 'es-cli-'); file_put_contents($file, '123');
        try {
            self::assertSame(123, $service->stop($file, true));
            self::assertSame([[123, 0], [123, SIGKILL], [123, 0]], $service->signals);
            self::assertFileDoesNotExist($file);
        } finally { if (is_file($file)) { unlink($file); } }
    }

    public static function serverPermissionDenials(): iterable
    {
        yield '停止前' => ['stop', false, [[123, 0]]];
        yield '等待退出时' => ['stop', true, [[123, 0], [123, SIGTERM], [123, 0]]];
        yield '重载前' => ['reload', false, [[123, 0]]];
    }

    #[DataProvider('serverPermissionDenials')]
    public function testPermissionDeniedKeepsServerPidFile(string $operation, bool $initialProbeSucceeds, array $expectedSignals): void
    {
        $service = new class($initialProbeSucceeds) extends ServerControlService {
            public array $signals = [];
            public function __construct(private bool $initialProbeSucceeds) {}

            protected function signal(int $pid, int $signal): bool
            {
                $this->signals[] = [$pid, $signal];
                return $signal !== 0 || ($this->initialProbeSucceeds && count($this->signals) === 1);
            }

            protected function signalError(): int { return 1; }
        };
        $file = tempnam(sys_get_temp_dir(), 'es-cli-');
        file_put_contents($file, '123');
        try {
            $this->expectExceptionMessage('cannot check server PID 123: ' . swoole_strerror(1));
            $service->{$operation}($file);
        } finally {
            self::assertFileExists($file);
            self::assertSame('123', file_get_contents($file));
            self::assertSame($expectedSignals, $service->signals);
            unlink($file);
        }
    }

    public function testCrontabReloadUsesSharedProcessServiceWithoutDispatchingAnotherCommand(): void
    {
        $processes = new class extends \EasySwoole\EasySwoole\Command\ProcessControlService {
            public ?\EasySwoole\EasySwoole\Command\ProcessSelection $selection = null;
            public function terminate(\EasySwoole\EasySwoole\Command\ProcessSelection $selection, Console $console): CommandResult {
                $this->selection = $selection;
                return CommandResult::success('sent', ['pid' => 123]);
            }
        };
        $command = new class($processes) extends \EasySwoole\EasySwoole\Command\DefaultCommand\CrontabReloadAction {
            protected function processGroup(): string { return 'App.test.Crontab'; }
        };
        $app = new \EasySwoole\Command\Application(); $app->addCommand(new \EasySwoole\EasySwoole\Command\DefaultCommand\CrontabCommand(['reload' => $command]));
        $result = $app->dispatch(['crontab', 'reload', '--force']);
        self::assertSame(0, $result->exitCode);
        self::assertSame(['pid' => 123], $result->data);
        self::assertSame('App.test.Crontab', $processes->selection->group);
        self::assertTrue($processes->selection->force);
    }

    public function testMissingCrontabGroupFailsWhileProcessKillRemainsIdempotent(): void
    {
        $runtime = new class extends RuntimeClient {
            public function call(string $action, array $arguments = []): \EasySwoole\Bridge\Package
            {
                $package = new \EasySwoole\Bridge\Package();
                $package->setStatus(\EasySwoole\Bridge\StatusEnum::SUCCESS);
                $package->setArgs([]);
                return $package;
            }
        };
        $service = new \EasySwoole\EasySwoole\Command\ProcessControlService($runtime);
        $reload = new class($service) extends \EasySwoole\EasySwoole\Command\DefaultCommand\CrontabReloadAction {
            protected function processGroup(): string { return 'App.test.Crontab'; }
        };
        $app = new \EasySwoole\Command\Application();
        $app->addCommand(new \EasySwoole\EasySwoole\Command\DefaultCommand\CrontabCommand(['reload' => $reload]));
        $app->addCommand(new \EasySwoole\EasySwoole\Command\DefaultCommand\ProcessCommand([
            'kill' => new \EasySwoole\EasySwoole\Command\DefaultCommand\ProcessKillAction($service),
        ]));

        $result = $app->dispatch(['crontab', 'reload']);
        self::assertSame(1, $result->exitCode);
        self::assertSame([], $result->data);
        self::assertStringContainsString('App.test.Crontab', $result->message);

        $result = $app->dispatch(['process', 'kill', '--pid=123']);
        self::assertSame(0, $result->exitCode);
        self::assertSame([], $result->data);
        self::assertSame('No processes matched the specified filters.', $result->message);
    }

    public function testMalformedBridgeRowsNeverReachProcessSignalling(): void
    {
        $runtime = new class extends RuntimeClient {
            public function call(string $action, array $arguments = []): \EasySwoole\Bridge\Package {
                $package = new \EasySwoole\Bridge\Package();
                $package->setStatus(\EasySwoole\Bridge\StatusEnum::SUCCESS);
                $package->setArgs([['pid' => 123, 'name' => 'valid'], ['pid' => 0, 'name' => 'invalid']]);
                return $package;
            }
        };
        $service = new class($runtime) extends \EasySwoole\EasySwoole\Command\ProcessControlService {
            public array $signals = [];
            protected function signalProcess(int $pid, int $signal): bool { $this->signals[] = [$pid, $signal]; return true; }
        };
        $app = new \EasySwoole\Command\Application();
        $app->addCommand(new \EasySwoole\EasySwoole\Command\DefaultCommand\ProcessCommand([
            'kill' => new \EasySwoole\EasySwoole\Command\DefaultCommand\ProcessKillAction($service),
        ]));
        $result = $app->dispatch(['process', 'kill', '--pid=123']);
        self::assertSame(1, $result->exitCode);
        self::assertSame([], $service->signals);
        self::assertSame('invalid process list returned by Bridge', $result->message);
    }
}
