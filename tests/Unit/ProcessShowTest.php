<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\CommandLine;
use EasySwoole\Command\Bean\ExecStatusEnum;
use EasySwoole\Command\Bean\Result;
use EasySwoole\EasySwoole\Command\DefaultCommand\Process;
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
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                    $result->msg = 'Bridge unavailable';
                }
                return $this->processes;
            }
        };
        $result = new Result();
        $result->status = ExecStatusEnum::OK;
        ($command->getActions()['show']->getCallback())(new Caller('process', 'show', new CommandLine()), $result);
        return $result;
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
        self::assertSame(ExecStatusEnum::OK, $result->status);
        self::assertSame($processes, $result->result);
        self::assertStringContainsString('Processes (total: 3)', $result->msg);
        self::assertStringContainsString('App.dev.TaskWorker (2)', $result->msg);
        self::assertStringContainsString('[1] App.dev.TaskWorker.0   PID 101', $result->msg);
        self::assertStringContainsString('[2] App.dev.TaskWorker.1   PID 202', $result->msg);
        self::assertStringContainsString('[3] App.dev.Worker.0   PID 303', $result->msg);
        self::assertStringContainsString('Memory 4 MB', $result->msg);
        self::assertStringContainsString('Memory 0 B', $result->msg);
        self::assertStringContainsString('Memory 1.5 KB', $result->msg);
        self::assertStringContainsString('Peak 6 MB', $result->msg);
        self::assertStringContainsString('processUpTime ' . date('Y-m-d H:i:s', $started), $result->msg);
        self::assertStringContainsString('lastHeartBeat ' . date('Y-m-d H:i:s', $heartbeat), $result->msg);
        self::assertStringContainsString('Custom process', $result->msg);
        self::assertStringContainsString('Worker process', $result->msg);
        self::assertStringNotContainsString('┌', $result->msg);
        self::assertStringNotContainsString('│', $result->msg);
    }

    public function testMissingMetricsAreShownAsUnknown(): void
    {
        $result = $this->show([['pid' => 101, 'name' => 'Worker.0']]);
        self::assertStringContainsString('Ungrouped (1)', $result->msg);
        self::assertStringContainsString('Memory -   processUpTime -', $result->msg);
        self::assertStringContainsString('Peak -   lastHeartBeat -', $result->msg);
    }

    public function testEmptyListHasClearMessage(): void
    {
        $result = $this->show([]);
        self::assertSame([], $result->result);
        self::assertSame('No running processes.', $result->msg);
    }

    public function testBridgeFailureIsNotOverwrittenByFormatting(): void
    {
        $result = $this->show(null);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertSame('Bridge unavailable', $result->msg);
    }
}
