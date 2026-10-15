<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\ExecStatusEnum;
use EasySwoole\Command\Bean\Result;
use EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Command\DefaultCommand\Process;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProcessKillTest extends TestCase
{
    private function rows(): array
    {
        return [
            101 => ['pid' => 101, 'memoryUsage' => 4194304, 'name' => 'Demo.dev.TaskWorker.0', 'group' => 'Demo.dev.TaskWorker', 'startUpTime' => 1700000000],
            202 => ['pid' => 202, 'name' => 'Demo.dev.TaskWorker.1', 'group' => 'Demo.dev.TaskWorker', 'startUpTime' => 1700000001],
            303 => ['pid' => 303, 'name' => 'Demo.dev.Worker.0', 'group' => 'Demo.dev.Worker', 'startUpTime' => 1700000002],
        ];
    }

    private function command(): Process
    {
        return new class extends Process {
            public array $snapshots = [];
            public array $inputs = [];
            public array $signals = [];
            public array $prompts = [];
            public int $fetches = 0;
            public bool $interactive = true;
            public bool $signalResult = true;

            protected function fetchProcessList(Result $result): ?array
            {
                ++$this->fetches;
                $snapshot = array_shift($this->snapshots);
                if ($snapshot === null) {
                    $result->status = ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL;
                    $result->msg = 'Bridge unavailable';
                }
                return $snapshot;
            }
            protected function isInteractive(): bool { return $this->interactive; }
            protected function readInput(string $prompt): ?string
            {
                $this->prompts[] = $prompt;
                return array_shift($this->inputs);
            }
            protected function signalProcess(int $pid, int $signal): bool
            {
                $this->signals[] = [$pid, $signal];
                return $this->signalResult;
            }
        };
    }

    private function execute(Process $command, array $options = []): array
    {
        $caller = new Caller('process', 'kill', Utility::parseArgv($options));
        $result = new Result();
        $result->status = ExecStatusEnum::OK;
        $action = $command->getActions()['kill'];
        ob_start();
        try {
            if ($action->__preCallCallback($caller, $result)) {
                ($action->getCallback())($caller, $result);
            }
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        return [$result, $output];
    }

    public function testInteractiveSelectionRetriesAndRequiresConfirmation(): void
    {
        $command = $this->command();
        $command->snapshots = [$this->rows(), $this->rows()];
        $command->inputs = ['invalid', '9', '2', 'y'];
        [$result, $output] = $this->execute($command);
        self::assertSame([[202, SIGTERM]], $command->signals);
        self::assertSame(2, $command->fetches);
        self::assertStringContainsString('Demo.dev.TaskWorker.1', $output);
        self::assertStringContainsString('Invalid selection', $output);
        self::assertStringContainsString('[0] Cancel', $output);
        self::assertStringContainsString('Memory 4 MB', $output);
        self::assertStringContainsString('processUpTime ' . date('Y-m-d H:i:s', 1700000000), $output);
        self::assertStringContainsString('PID 202', end($command->prompts));
        self::assertSame(ExecStatusEnum::OK, $result->status);
        self::assertStringContainsString('signal sent', $result->msg);
    }

    public function testMultipleSelectionsAreDeduplicatedAndConfirmedTogether(): void
    {
        $command = $this->command();
        $command->snapshots = [$this->rows(), $this->rows()];
        $command->inputs = ['1,2,1', 'y'];
        [$result, $output] = $this->execute($command);
        self::assertSame([[101, SIGTERM], [202, SIGTERM]], $command->signals);
        self::assertStringContainsString('PID 101', end($command->prompts));
        self::assertStringContainsString('PID 202', end($command->prompts));
        self::assertStringNotContainsString('┌', $output . $result->msg);
        self::assertSame(ExecStatusEnum::OK, $result->status);
    }

    public function testChangedMultiSelectionAbortsBeforeSignallingAnyProcess(): void
    {
        $current = $this->rows();
        unset($current[202]);
        $command = $this->command();
        $command->snapshots = [$this->rows(), $current];
        $command->inputs = ['1,2', 'y'];
        [$result] = $this->execute($command);
        self::assertSame([], $command->signals);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
    }

    public static function cancellations(): iterable
    {
        yield 'zero' => [['0']];
        yield 'blank' => [['']];
        yield 'quit' => [['q']];
        yield 'EOF at selection' => [[null]];
        yield 'default no' => [['1', '']];
        yield 'explicit no' => [['1', 'n']];
        yield 'EOF at confirmation' => [['1', null]];
    }

    #[DataProvider('cancellations')]
    public function testCancellationNeverSignalsOrRefreshes(array $inputs): void
    {
        $command = $this->command();
        $command->snapshots = [$this->rows()];
        $command->inputs = $inputs;
        [$result] = $this->execute($command);
        self::assertSame([], $command->signals);
        self::assertSame(1, $command->fetches);
        self::assertSame([], $result->result);
        self::assertSame('process selection cancelled', $result->msg);
    }

    public static function forceOptions(): iterable
    {
        yield [[], SIGTERM];
        yield [['--force=false'], SIGTERM];
        yield [['--force=true'], SIGKILL];
        yield [['--force'], SIGKILL];
    }

    #[DataProvider('forceOptions')]
    public function testConfirmationShowsTheSignalBeingSent(array $options, int $signal): void
    {
        $command = $this->command();
        $command->snapshots = [$this->rows(), $this->rows()];
        $command->inputs = ['1', 'yes'];
        $this->execute($command, $options);
        self::assertSame([[101, $signal]], $command->signals);
        self::assertStringContainsString($signal === SIGKILL ? 'SIGKILL' : 'SIGTERM', end($command->prompts));
    }

    public static function selectors(): iterable
    {
        yield [['--pid=202'], [202]];
        yield [['--group=Demo.dev.TaskWorker'], [101, 202]];
        yield [['--name=Demo.dev.Worker.0'], [303]];
        yield [['--pid=101', '--name=Demo.dev.Worker.0'], [101, 303]];
    }

    #[DataProvider('selectors')]
    public function testExplicitSelectorsKeepScriptBehavior(array $options, array $pids): void
    {
        $command = $this->command();
        $command->interactive = false;
        $command->snapshots = [$this->rows()];
        [$result, $output] = $this->execute($command, $options);
        self::assertSame(array_map(static fn ($pid) => [$pid, SIGTERM], $pids), $command->signals);
        self::assertSame([], $command->prompts);
        self::assertSame('', $output);
        self::assertSame(ExecStatusEnum::OK, $result->status);
    }

    public static function changedIdentities(): iterable
    {
        yield ['pid', 999];
        yield ['name', 'replacement'];
        yield ['group', 'replacement'];
        yield ['startUpTime', 1800000000];
    }

    #[DataProvider('changedIdentities')]
    public function testProcessIdentityIsRecheckedAfterConfirmation(string $field, mixed $value): void
    {
        $changed = $this->rows();
        $changed[101][$field] = $value;
        $command = $this->command();
        $command->snapshots = [$this->rows(), $changed];
        $command->inputs = ['1', 'y'];
        [$result] = $this->execute($command);
        self::assertSame([], $command->signals);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertStringContainsString('exited or changed', $result->msg);
    }

    public function testSelectedProcessDisappearingDoesNotSignal(): void
    {
        $command = $this->command();
        $command->snapshots = [$this->rows(), []];
        $command->inputs = ['1', 'y'];
        [$result] = $this->execute($command);
        self::assertSame([], $command->signals);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
    }

    public function testNonTerminalRequiresAnExplicitSelector(): void
    {
        $command = $this->command();
        $command->interactive = false;
        $command->snapshots = [$this->rows()];
        [$result] = $this->execute($command);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertSame([], $command->signals);
        self::assertSame([], $command->prompts);
        self::assertStringContainsString('requires a terminal', $result->msg);
    }

    public function testEmptyProcessListDoesNotPrompt(): void
    {
        $command = $this->command();
        $command->snapshots = [[]];
        [$result] = $this->execute($command);
        self::assertSame('no processes available', $result->msg);
        self::assertSame([], $command->prompts);
    }

    public static function bridgeFailures(): iterable
    {
        yield 'initial failure' => [[null], []];
        yield 'refresh failure' => [[true, null], ['1', 'y']];
    }

    #[DataProvider('bridgeFailures')]
    public function testBridgeFailureNeverSignals(array $snapshots, array $inputs): void
    {
        $command = $this->command();
        $command->snapshots = array_map(fn ($item) => $item === true ? $this->rows() : $item, $snapshots);
        $command->inputs = $inputs;
        [$result] = $this->execute($command);
        self::assertSame([], $command->signals);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertSame('Bridge unavailable', $result->msg);
    }

    public function testSignalFailureIsReported(): void
    {
        $command = $this->command();
        $command->snapshots = [$this->rows()];
        $command->signalResult = false;
        [$result] = $this->execute($command, ['--pid=101']);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertStringContainsString('signal failed', $result->msg);
    }

    public static function invalidOptions(): iterable
    {
        yield [['--pid=0']];
        yield [['--pid=-1']];
        yield [['--pid']];
        yield [['--force=invalid']];
        yield [['--name=']];
    }

    #[DataProvider('invalidOptions')]
    public function testInvalidOptionsAreRejectedBeforeProcessLookup(array $options): void
    {
        $command = $this->command();
        [$result] = $this->execute($command, $options);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_OPTION_VALIDATE_FAIL, $result->status);
        self::assertSame(0, $command->fetches);
        self::assertSame([], $command->signals);
    }
}
