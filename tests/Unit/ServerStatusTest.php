<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\CommandLine;
use EasySwoole\Command\Bean\ExecStatusEnum;
use EasySwoole\Command\Bean\Result;
use EasySwoole\EasySwoole\Command\DefaultCommand\Server;
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
        $result = new Result();
        $result->status = ExecStatusEnum::OK;
        ($command->getActions()['status']->getCallback())(new Caller('server', 'status', new CommandLine()), $result);
        return $result;
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
        self::assertSame(ExecStatusEnum::OK, $result->status);
        self::assertSame($data, $result->result);
        self::assertStringContainsString("Server status\n\nEnvironment\n", $result->msg);
        self::assertStringContainsString("\n\nRuntime\n", $result->msg);
        foreach (['main server', 'listen address', 'listen port', 'daemonize', 'reload_async',
            'optional', 'settings', 'start_time', 'worker_num', 'connection_num', 'request_count',
            'total_send_bytes', 'coroutine_num', 'new_metric'] as $key) {
            self::assertMatchesRegularExpression('/' . preg_quote($key, '/') . ' +: /', $result->msg);
        }
        foreach ([' : false', ' : true', ' : -', ' : 0', ' : 99', '{"host":"http://localhost"}', date('Y-m-d H:i:s', 1700000000)] as $value) {
            self::assertStringContainsString($value, $result->msg);
        }
        self::assertStringNotContainsString('┌', $result->msg);
        self::assertStringNotContainsString('│', $result->msg);
        self::assertStringNotContainsString("\033", $result->msg);
    }

    public function testEmptyRuntimeAndMissingStartTimeAreSupported(): void
    {
        $result = $this->execute(['env' => [], 'runtime' => []]);
        self::assertSame(ExecStatusEnum::OK, $result->status);
        self::assertSame(2, substr_count($result->msg, 'No data available.'));
        $result = $this->execute(['env' => [], 'runtime' => ['worker_num' => 1]]);
        self::assertSame(ExecStatusEnum::OK, $result->status);
        self::assertStringNotContainsString('start_time', $result->msg);
    }

    public function testZeroStartTimeIsShownAsUnknown(): void
    {
        $result = $this->execute(['env' => [], 'runtime' => ['start_time' => 0]]);
        self::assertMatchesRegularExpression('/start_time +: -/', $result->msg);
    }

    public function testRuntimePairsMetricsAndKeepsOddLastItem(): void
    {
        $result = $this->execute(['env' => [], 'runtime' => [
            'start_time' => 1700000000, 'worker_num' => 2,
            'connection_num' => 0, 'request_count' => 123,
            'coroutine_num' => 1,
        ]]);
        $runtime = explode("\n\nRuntime\n", $result->msg)[1];
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
        $runtime = explode("\n\nRuntime\n", $result->msg)[1];
        $rows = explode(PHP_EOL, $runtime);
        self::assertCount(2, $rows);
        self::assertStringContainsString($value . '    second', $rows[0]);
        self::assertSame(strpos($rows[0], 'second'), strpos($rows[1], 'fourth'));
        self::assertMatchesRegularExpression('/main server +: SWOOLE_WEB +listen port +: 9501/', $result->msg);
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
        $section = explode("\n\nRuntime\n", explode("\n\nEnvironment\n", $result->msg)[1])[0];
        $rows = explode(PHP_EOL, $section);
        self::assertCount(7, $rows);
        self::assertMatchesRegularExpression('/main server +: SWOOLE_WEB +listen address +: 127.0.0.1/', $rows[0]);
        self::assertMatchesRegularExpression('/listen port +: 9501 +run mode +: dev/', $rows[1]);
        self::assertStringContainsString('daemonize', $rows[2]);
        self::assertSame(1, substr_count($rows[2], ' : '));
        foreach (array_keys($paths) as $index => $key) {
            self::assertMatchesRegularExpression('/^  ' . preg_quote($key, '/') . ' +: ' . preg_quote($paths[$key], '/') . '$/', $rows[$index + 3]);
        }
        self::assertSame($environment, $result->result['env']);
        self::assertSame(strpos($rows[0], 'listen address'), strpos($rows[1], 'run mode'));
        self::assertLessThan(80, strlen($rows[0]));
    }

    public function testEnvironmentContainingOnlyPathsStillDisplaysThem(): void
    {
        $result = $this->execute(['env' => ['pid_file' => '/tmp/app.pid', 'log_file' => '/tmp/app.log'], 'runtime' => []]);
        self::assertMatchesRegularExpression('/pid_file +: \/tmp\/app.pid\n  log_file +: \/tmp\/app.log/', $result->msg);
        self::assertStringNotContainsString('pid_file : /tmp/app.pid    log_file', $result->msg);
    }

    public function testBridgeFailureSetsCommandFailure(): void
    {
        $result = $this->execute(null, StatusEnum::CONNECT_ERROR);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertSame('Bridge unavailable', $result->msg);
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
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertSame('invalid server status returned by Bridge', $result->msg);
    }

    public function testRequestExceptionIsReported(): void
    {
        $command = new class extends Server {
            protected function requestServerStatus(): Package { throw new \RuntimeException('connection error'); }
        };
        $result = new Result();
        $result->status = ExecStatusEnum::OK;
        ($command->getActions()['status']->getCallback())(new Caller('server', 'status', new CommandLine()), $result);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertSame('connection error', $result->msg);
    }
}
