<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Bridge\{Package, StatusEnum};
use EasySwoole\Command\Application;
use EasySwoole\EasySwoole\Command\RuntimeClient;
use EasySwoole\EasySwoole\Command\DefaultCommand\{TaskReloadAction, TaskShowAction};
use PHPUnit\Framework\TestCase;

final class TaskCommandTest extends TestCase
{
    private function runtime(mixed $rows, StatusEnum $status = StatusEnum::SUCCESS): RuntimeClient
    {
        return new class($rows, $status) extends RuntimeClient {
            public function __construct(private mixed $rows, private StatusEnum $status) {}
            public function call(string $action, array $arguments = []): Package {
                $package = new Package(); $package->setStatus($this->status); $package->setArgs($this->rows); $package->setMsg('offline');
                return $package;
            }
        };
    }

    public function testTaskReloadDeduplicatesPidsAndPreservesFalseForce(): void
    {
        $command = new class($this->runtime([123, 123, 456])) extends TaskReloadAction {
            public array $signals = [];
            protected function signalTaskWorker(int $pid, int $signal): bool { $this->signals[] = [$pid, $signal]; return $pid !== 456; }
        };
        $app = new Application(); $app->addCommand(new \EasySwoole\EasySwoole\Command\DefaultCommand\TaskCommand(['reload' => $command]));
        $result = $app->dispatch(['task', 'reload', '--force=false']);
        self::assertSame([[123, SIGTERM], [456, SIGTERM]], $command->signals);
        self::assertSame(1, $result->exitCode);
        self::assertSame(['signal' => SIGTERM, 'sent' => [123], 'failed' => [456]], $result->data);
    }

    public function testMalformedTaskPidListAbortsBeforeAnySignal(): void
    {
        $command = new class($this->runtime([123, 0])) extends TaskReloadAction {
            public array $signals = [];
            protected function signalTaskWorker(int $pid, int $signal): bool { $this->signals[] = [$pid, $signal]; return true; }
        };
        $app = new Application(); $app->addCommand(new \EasySwoole\EasySwoole\Command\DefaultCommand\TaskCommand(['reload' => $command]));
        $result = $app->dispatch(['task', 'reload']);
        self::assertSame(1, $result->exitCode);
        self::assertSame([], $command->signals);
    }

    public function testTaskShowRetainsStructuredRowsAndFormatsStatus(): void
    {
        $rows = [0 => ['pid' => 123, 'accepting' => 0, 'running' => 1, 'success' => 5, 'fail' => 2, 'startUpTime' => 1700000000]];
        $app = new Application(); $app->addCommand(new \EasySwoole\EasySwoole\Command\DefaultCommand\TaskCommand(['show' => new TaskShowAction($this->runtime($rows))]));
        $result = $app->dispatch(['task', 'show']);
        self::assertSame(0, $result->exitCode);
        self::assertSame($rows, $result->data);
        self::assertStringContainsString('State draining', $result->message);
        self::assertStringContainsString('Running 1   Success 5   Fail 2', $result->message);
    }

    public function testTaskShowReturnsBridgeFailure(): void
    {
        $app = new Application(); $app->addCommand(new \EasySwoole\EasySwoole\Command\DefaultCommand\TaskCommand(['show' => new TaskShowAction($this->runtime(null, StatusEnum::CONNECT_ERROR))]));
        $result = $app->dispatch(['task', 'show']);
        self::assertSame(1, $result->exitCode);
        self::assertSame('offline', $result->message);
    }
}
