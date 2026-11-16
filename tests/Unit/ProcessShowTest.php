<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Command\Result\CommandResult as Result;
use EasySwoole\Command\Application;
use EasySwoole\Command\Input\Input;
use EasySwoole\Command\Console\Console;
use EasySwoole\EasySwoole\Test\Support\TestConsole;
use EasySwoole\EasySwoole\Command\ProcessControlService as Process;
use EasySwoole\EasySwoole\Command\DefaultCommand\ProcessKillAction;
use PHPUnit\Framework\TestCase;

final class ProcessShowTest extends TestCase
{
    private function show(?array $processes): Result
    {
        $command = new class($processes) extends Process {
            public function __construct(private ?array $processes) { parent::__construct(); }
            protected function fetchProcessList(Result $result): ?array
            {
                if ($this->processes === null) {
                    $result->exitCode = 1;
                    $result->message = 'Bridge unavailable';
                }
                return $this->processes;
            }
        };
        return $command->listing();
    }

    public function testReadableGroupedListPreservesProcessDetailsAndRawData(): void
    {
        $started = 1700000000;
        $heartbeat = 1700000030;
        $processes = [
            101 => ['pid' => 101, 'name' => 'App.dev.TaskWorker.0', 'group' => 'App.dev.TaskWorker',
                'memoryUsage' => 4194304, 'memoryPeakUsage' => 6291456, 'startUpTime' => $started,
                'lastHeartBeat' => $heartbeat, 'customProcess' => 1],
            202 => ['pid' => 202, 'name' => 'App.dev.TaskWorker.1', 'group' => 'App.dev.TaskWorker',
                'memoryUsage' => 0, 'memoryPeakUsage' => 0, 'startUpTime' => $started,
                'lastHeartBeat' => $heartbeat, 'customProcess' => 1],
            303 => ['pid' => 303, 'name' => 'App.dev.Worker.0', 'group' => 'App.dev.Worker',
                'memoryUsage' => 1536, 'memoryPeakUsage' => 2048, 'startUpTime' => $started,
                'lastHeartBeat' => $heartbeat, 'customProcess' => 0],
        ];
        $result = $this->show($processes);
        self::assertSame(0, $result->exitCode);
        self::assertSame($processes, $result->data);
        self::assertStringContainsString('Processes (total: 3)', $result->message);
        self::assertStringContainsString('App.dev.TaskWorker (2)', $result->message);
        self::assertStringContainsString('[1] App.dev.TaskWorker.0   PID 101', $result->message);
        self::assertStringContainsString('[2] App.dev.TaskWorker.1   PID 202', $result->message);
        self::assertStringContainsString('[3] App.dev.Worker.0   PID 303', $result->message);
        self::assertStringContainsString('Memory 4 MB', $result->message);
        self::assertStringContainsString('Memory 0 B', $result->message);
        self::assertStringContainsString('Memory 1.5 KB', $result->message);
        self::assertStringContainsString('Peak 6 MB', $result->message);
        self::assertStringContainsString('processUpTime ' . date('Y-m-d H:i:s', $started), $result->message);
        self::assertStringContainsString('lastHeartBeat ' . date('Y-m-d H:i:s', $heartbeat), $result->message);
        self::assertStringContainsString('Custom process', $result->message);
        self::assertStringContainsString('Worker process', $result->message);
        self::assertStringNotContainsString('┌', $result->message);
        self::assertStringNotContainsString('│', $result->message);
    }

    public function testMissingMetricsAreShownAsUnknown(): void
    {
        $result = $this->show([['pid' => 101, 'name' => 'Worker.0']]);
        self::assertStringContainsString('Ungrouped (1)', $result->message);
        self::assertStringContainsString('Memory -   processUpTime -', $result->message);
        self::assertStringContainsString('Peak -   lastHeartBeat -', $result->message);
    }

    public function testEmptyListHasClearMessage(): void
    {
        $result = $this->show([]);
        self::assertSame([], $result->data);
        self::assertSame('No running processes.', $result->message);
    }

    public function testBridgeFailureIsNotOverwrittenByFormatting(): void
    {
        $result = $this->show(null);
        self::assertSame(1, $result->exitCode);
        self::assertSame('Bridge unavailable', $result->message);
    }
}
