<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\Command\Result\CommandResult as Result;
use EasySwoole\Command\Application;
use EasySwoole\Command\Event\ExceptionEvent;
use EasySwoole\EasySwoole\Command\CommandRunner;
use EasySwoole\EasySwoole\Command\DefaultCommand\ServerCommand;
use EasySwoole\EasySwoole\Command\DefaultCommand\ServerStatusAction as Server;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ServerStatusTest extends TestCase
{
    private function execute(mixed $data, StatusEnum $status = StatusEnum::SUCCESS): Result
    {
        $package = new Package();
        $package->setStatus($status);
        $package->setArgs($data);
        $package->setMsg('Bridge unavailable');
        $command = new class($package) extends Server {
            public function __construct(private Package $package) { parent::__construct(); }
            protected function requestServerStatus(): Package { return $this->package; }
        };
        $app = new Application();
        $app->addCommand(new ServerCommand(['status' => $command]));
        return $app->dispatch(['server', 'status']);
    }

    public function testReadableSectionsRetainAllMetricsAndRawData(): void
    {
        $data = ['env' => ['main server' => 'SWOOLE_WEB', 'listen address' => '127.0.0.1',
            'listen port' => 9501, 'daemonize' => false, 'reload_async' => true,
            'optional' => null, 'settings' => ['host' => 'http://localhost']],
            'runtime' => ['start_time' => 1700000000, 'worker_num' => 2, 'connection_num' => 0,
                'request_count' => 123, 'total_send_bytes' => 456, 'coroutine_num' => 1,
                'new_metric' => 99]];
        $result = $this->execute($data);
        self::assertSame(0, $result->exitCode);
        self::assertSame($data, $result->data);
        self::assertStringContainsString("Server status\n\nEnvironment\n", $result->message);
        self::assertStringContainsString("\n\nRuntime\n", $result->message);
        foreach (['main server', 'listen address', 'listen port', 'daemonize', 'reload_async',
            'optional', 'settings', 'start_time', 'worker_num', 'connection_num', 'request_count',
            'total_send_bytes', 'coroutine_num', 'new_metric'] as $key) {
            self::assertMatchesRegularExpression('/' . preg_quote($key, '/') . ' +: /', $result->message);
        }
        foreach ([' : false', ' : true', ' : -', ' : 0', ' : 99', '{"host":"http://localhost"}', date('Y-m-d H:i:s', 1700000000)] as $value) {
            self::assertStringContainsString($value, $result->message);
        }
        self::assertStringNotContainsString('┌', $result->message);
        self::assertStringNotContainsString('│', $result->message);
        self::assertStringNotContainsString("\033", $result->message);
    }

    public function testEmptyRuntimeAndMissingStartTimeAreSupported(): void
    {
        $result = $this->execute(['env' => [], 'runtime' => []]);
        self::assertSame(0, $result->exitCode);
        self::assertSame(2, substr_count($result->message, 'No data available.'));
        $result = $this->execute(['env' => [], 'runtime' => ['worker_num' => 1]]);
        self::assertSame(0, $result->exitCode);
        self::assertStringNotContainsString('start_time', $result->message);
    }

    public function testZeroStartTimeIsShownAsUnknown(): void
    {
        $result = $this->execute(['env' => [], 'runtime' => ['start_time' => 0]]);
        self::assertMatchesRegularExpression('/start_time +: -/', $result->message);
    }

    public function testRuntimePairsMetricsAndKeepsOddLastItem(): void
    {
        $result = $this->execute(['env' => [], 'runtime' => [
            'start_time' => 1700000000, 'worker_num' => 2,
            'connection_num' => 0, 'request_count' => 123,
            'coroutine_num' => 1,
        ]]);
        $runtime = explode("\n\nRuntime\n", $result->message)[1];
        $rows = explode(PHP_EOL, $runtime);
        self::assertCount(3, $rows);
        self::assertStringContainsString('start_time', $rows[0]);
        self::assertStringContainsString('worker_num', $rows[0]);
        self::assertStringContainsString('connection_num', $rows[1]);
        self::assertStringContainsString('request_count', $rows[1]);
        self::assertStringContainsString('coroutine_num', $rows[2]);
        self::assertSame(1, substr_count($rows[2], ' : '));
        self::assertSame(strpos($rows[0], 'worker_num'), strpos($rows[1], 'request_count'));
    }

    public function testRuntimeEvenItemsAndLongValuesDoNotOverlap(): void
    {
        $value = str_repeat('x', 100);
        $result = $this->execute(['env' => ['main server' => 'SWOOLE_WEB', 'listen port' => 9501],
            'runtime' => ['first' => $value, 'second' => 2, 'third' => 3, 'fourth' => 4]]);
        $runtime = explode("\n\nRuntime\n", $result->message)[1];
        $rows = explode(PHP_EOL, $runtime);
        self::assertCount(2, $rows);
        self::assertStringContainsString($value . '    second', $rows[0]);
        self::assertSame(strpos($rows[0], 'second'), strpos($rows[1], 'fourth'));
        self::assertMatchesRegularExpression('/main server +: SWOOLE_WEB +listen port +: 9501/', $result->message);
    }

    public function testEnvironmentPairsOrdinaryItemsAndKeepsEachPathOnItsOwnLine(): void
    {
        $paths = [
            'pid_file' => '/var/run/' . str_repeat('a', 100) . '/app.pid',
            'log_file' => '/var/log/app.swoole.log',
            'temp dir' => '/var/tmp/app',
            'log dir' => '/var/log/app',
        ];
        $environment = ['main server' => 'SWOOLE_WEB', 'pid_file' => $paths['pid_file'],
            'listen address' => '127.0.0.1', 'log_file' => $paths['log_file'],
            'listen port' => 9501, 'run mode' => 'dev', 'temp dir' => $paths['temp dir'],
            'log dir' => $paths['log dir'], 'daemonize' => false];
        $result = $this->execute(['env' => $environment, 'runtime' => []]);
        $section = explode("\n\nRuntime\n", explode("\n\nEnvironment\n", $result->message)[1])[0];
        $rows = explode(PHP_EOL, $section);
        self::assertCount(7, $rows);
        self::assertMatchesRegularExpression('/main server +: SWOOLE_WEB +listen address +: 127.0.0.1/', $rows[0]);
        self::assertMatchesRegularExpression('/listen port +: 9501 +run mode +: dev/', $rows[1]);
        self::assertStringContainsString('daemonize', $rows[2]);
        self::assertSame(1, substr_count($rows[2], ' : '));
        foreach (array_keys($paths) as $index => $key) {
            self::assertMatchesRegularExpression('/^  ' . preg_quote($key, '/') . ' +: ' . preg_quote($paths[$key], '/') . '$/', $rows[$index + 3]);
        }
        self::assertSame($environment, $result->data['env']);
        self::assertSame(strpos($rows[0], 'listen address'), strpos($rows[1], 'run mode'));
        self::assertLessThan(80, strlen($rows[0]));
    }

    public function testEnvironmentContainingOnlyPathsStillDisplaysThem(): void
    {
        $result = $this->execute(['env' => ['pid_file' => '/tmp/app.pid', 'log_file' => '/tmp/app.log'], 'runtime' => []]);
        self::assertMatchesRegularExpression('/pid_file +: \/tmp\/app.pid\n  log_file +: \/tmp\/app.log/', $result->message);
        self::assertStringNotContainsString('pid_file : /tmp/app.pid    log_file', $result->message);
    }

    public function testBridgeFailureSetsCommandFailure(): void
    {
        $result = $this->execute(null, StatusEnum::CONNECT_ERROR);
        self::assertSame(1, $result->exitCode);
        self::assertSame('Bridge unavailable', $result->message);
    }

    public static function malformed(): iterable
    {
        yield [null];
        yield [[]];
        yield [['env' => [], 'runtime' => 'invalid']];
        yield [['env' => null, 'runtime' => []]];
    }

    #[DataProvider('malformed')]
    public function testMalformedResponseIsReported(mixed $data): void
    {
        $result = $this->execute($data);
        self::assertSame(1, $result->exitCode);
        self::assertSame('invalid server status returned by Bridge', $result->message);
    }

    public function testRequestExceptionReachesCommandRunnerObserver(): void
    {
        $error = new \RuntimeException('connection error', 0, new \LogicException('original cause'));
        $action = new class($error) extends Server {
            public function __construct(private \Throwable $error) {}
            protected function requestServerStatus(): Package { throw $this->error; }
        };
        $runner = new class extends CommandRunner {
            protected function initializeRuntime(?string $mode): void {}
        };
        $command = new ServerCommand(['status' => $action]);
        $runner->registry()->replace($command->definition(), static fn () => $command);
        $events = [];
        $runner->onException(static function (ExceptionEvent $event) use (&$events): void {
            $events[] = $event;
        });

        $result = $runner->dispatch(['server', 'status', '--mode=dev']);

        self::assertSame(1, $result->exitCode);
        self::assertSame('connection error', $result->message);
        self::assertCount(1, $events);
        self::assertSame($error, $events[0]->error);
        self::assertSame('server', $events[0]->command);
        self::assertSame('status', $events[0]->action);
        self::assertSame('dev', $events[0]->input->option('mode'));
    }
}
