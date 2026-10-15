<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\ExecStatusEnum;
use EasySwoole\Command\Bean\Result;
use EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Bridge\Bridge;
use EasySwoole\EasySwoole\Command\DefaultCommand\Server;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ServerReloadTaskTest extends TestCase
{
    private mixed $previousBridge;
    private ?Bridge $fakeBridge = null;

    protected function setUp(): void
    {
        $this->previousBridge = (new \ReflectionProperty(Bridge::class, 'instance'))->getValue();
    }

    protected function tearDown(): void
    {
        (new \ReflectionProperty(Bridge::class, 'instance'))->setValue(null, $this->previousBridge);
    }

    private function command(): Server
    {
        return new class extends Server {
            public array $signals = [];
            public array $failedPids = [];
            protected function signalTaskWorker(int $pid, int $signal): bool
            {
                $this->signals[] = [$pid, $signal];
                return !in_array($pid, $this->failedPids, true);
            }
        };
    }

    private function bridge(mixed $pids, StatusEnum $status = StatusEnum::SUCCESS): void
    {
        $package = new Package();
        $package->setArgs($pids);
        $package->setStatus($status);
        $package->setMsg('bridge failure');
        // A concrete fake avoids PHPUnit's call-stack lookup inside Swoole coroutines.
        $bridge = new class($package) extends Bridge {
            public array $calls = [];
            public function __construct(private Package $response) {}
            public function call(string $command, $arg = null, float $timeout = 3.0): Package
            {
                $this->calls[] = [$command, $arg, $timeout];
                return $this->response;
            }
        };
        $this->fakeBridge = $bridge;
        (new \ReflectionProperty(Bridge::class, 'instance'))->setValue(null, $bridge);
    }

    private function execute(Server $command, array $options): Result
    {
        $caller = new Caller('server', 'reloadTask', Utility::parseArgv($options));
        $result = new Result();
        $result->status = ExecStatusEnum::OK;
        $action = $command->getActions()['reloadTask'];
        if ($action->__preCallCallback($caller, $result)) {
            ($action->getCallback())($caller, $result);
        }
        if ($this->fakeBridge !== null) {
            self::assertSame([['BaseService', ['action' => 'taskWorkerPidList'], 3.0]], $this->fakeBridge->calls);
        }
        return $result;
    }

    public static function forceOptions(): iterable
    {
        yield 'default' => [[], SIGTERM];
        yield 'true' => [['--force=true'], SIGKILL];
        yield 'false' => [['--force=false'], SIGTERM];
        yield 'flag' => [['--force'], SIGKILL];
        yield 'one' => [['--force=1'], SIGKILL];
        yield 'zero' => [['--force=0'], SIGTERM];
    }

    #[DataProvider('forceOptions')]
    public function testReloadSignalsOnlyReturnedTaskPids(array $options, int $signal): void
    {
        $this->bridge([1234, 5678, 1234]);
        $command = $this->command();
        $result = $this->execute($command, $options);
        self::assertSame([[1234, $signal], [5678, $signal]], $command->signals);
        self::assertSame(ExecStatusEnum::OK, $result->status);
        self::assertSame(['signal' => $signal, 'sent' => [1234, 5678], 'failed' => []], $result->result);
    }

    public function testInvalidForceIsRejectedBeforeBridgeCall(): void
    {
        $command = $this->command();
        $result = $this->execute($command, ['--force=invalid']);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_OPTION_VALIDATE_FAIL, $result->status);
        self::assertSame([], $command->signals);
    }

    public function testBridgeFailureDoesNotSignalWorkers(): void
    {
        $this->bridge(null, StatusEnum::CONNECT_ERROR);
        $command = $this->command();
        $result = $this->execute($command, []);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertSame('bridge failure', $result->msg);
        self::assertSame([], $command->signals);
    }

    public static function invalidPidLists(): iterable
    {
        yield [null];
        yield [[1234, 0]];
        yield [[-1]];
        yield [['1234']];
    }

    #[DataProvider('invalidPidLists')]
    public function testInvalidPidListCannotSignalAProcessGroup(mixed $pids): void
    {
        $this->bridge($pids);
        $command = $this->command();
        $result = $this->execute($command, []);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertSame([], $command->signals);
    }

    public function testEmptyListIsANoop(): void
    {
        $this->bridge([]);
        $command = $this->command();
        $result = $this->execute($command, []);
        self::assertSame(ExecStatusEnum::OK, $result->status);
        self::assertSame([], $command->signals);
    }

    public function testSignalFailureIsReportedWhileOtherWorkersAreAttempted(): void
    {
        $this->bridge([1234, 5678]);
        $command = $this->command();
        $command->failedPids = [1234];
        $result = $this->execute($command, []);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertSame(['signal' => SIGTERM, 'sent' => [5678], 'failed' => [1234]], $result->result);
        self::assertSame([[1234, SIGTERM], [5678, SIGTERM]], $command->signals);
    }
}
