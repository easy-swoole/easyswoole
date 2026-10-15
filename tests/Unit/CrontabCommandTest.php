<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\Command\Bean\Caller;
use EasySwoole\Command\Bean\ExecStatusEnum;
use EasySwoole\Command\Bean\Result;
use EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Command\Selector\CrontabSelector;
use EasySwoole\EasySwoole\Command\DefaultCommand\Crontab;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CrontabCommandTest extends TestCase
{
    private function tasks(): array
    {
        return ['Heartbeat' => ['taskRule' => '* * * * *', 'isStop' => 0, 'taskNextRunTime' => 1700000000],
            'Cleanup' => ['taskRule' => '0 0 * * *', 'isStop' => 1, 'taskNextRunTime' => 1700000000]];
    }

    private function package(mixed $args = null, StatusEnum $status = StatusEnum::SUCCESS): Package
    {
        $package = new Package();
        $package->setStatus($status);
        $package->setArgs($args);
        $package->setMsg($status === StatusEnum::SUCCESS ? 'done' : 'Bridge failure');
        return $package;
    }

    private function command(): Crontab
    {
        return new class extends Crontab {
            public array $responses = [];
            public array $calls = [];
            public array $inputs = [];
            public array $prompts = [];
            public bool $interactive = true;
            protected function isInteractive(): bool { return $this->interactive; }
            protected function readInput(string $prompt): ?string
            {
                $this->prompts[] = $prompt;
                return array_shift($this->inputs);
            }
            protected function callBridge(string $action, array $args = []): Package
            {
                $this->calls[] = [$action, $args];
                $response = array_shift($this->responses);
                if ($response instanceof \Throwable) { throw $response; }
                return $response;
            }
        };
    }

    private function execute(Crontab $command, string $action, array $options = []): array
    {
        $caller = new Caller('crontab', $action, Utility::parseArgv($options));
        $result = new Result();
        $result->status = ExecStatusEnum::OK;
        ob_start();
        try {
            $handler = $command->getActions()[$action];
            if ($handler->__preCallCallback($caller, $result)) {
                ($handler->getCallback())($caller, $result);
            }
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        return [$result, $output];
    }

    public static function actions(): iterable
    {
        yield ['stop', 'stopCrontabRule'];
        yield ['resume', 'resumeCrontabRule'];
        yield ['runJobNow', 'runCrontabJobNow'];
    }

    #[DataProvider('actions')]
    public function testMissingNamePromptsForMultipleTasksAndConfirms(string $action, string $bridgeAction): void
    {
        $command = $this->command();
        $command->inputs = ['invalid', '1,2,1', 'y'];
        $command->responses = [$this->package($this->tasks()), $this->package($this->tasks()), $this->package(), $this->package()];
        [$result, $output] = $this->execute($command, $action);
        self::assertSame([['crontabInfo', []], ['crontabInfo', []],
            [$bridgeAction, ['taskName' => 'Heartbeat']], [$bridgeAction, ['taskName' => 'Cleanup']]], $command->calls);
        self::assertSame(ExecStatusEnum::OK, $result->status);
        self::assertStringContainsString('Select crontab tasks', $output);
        self::assertStringContainsString('Rule * * * * *', $output);
        self::assertStringContainsString('State stopped', $output);
        self::assertStringContainsString('Invalid selection', $output);
        self::assertStringContainsString('Heartbeat, Cleanup', end($command->prompts));
        self::assertSame(['Heartbeat', 'Cleanup'], array_keys($result->result));
    }

    #[DataProvider('actions')]
    public function testExplicitNameWorksWithoutTerminalOrExtraQueries(string $action, string $bridgeAction): void
    {
        $command = $this->command();
        $command->interactive = false;
        $command->responses = [$this->package()];
        [$result, $output] = $this->execute($command, $action, ['--taskName=Heartbeat']);
        self::assertSame([[$bridgeAction, ['taskName' => 'Heartbeat']]], $command->calls);
        self::assertSame('done', $result->msg);
        self::assertSame('', $output);
        self::assertSame([], $command->prompts);
    }

    public static function cancellations(): iterable
    {
        yield [['0']];
        yield [['']];
        yield [[null]];
        yield [['q']];
        yield [['1', '']];
        yield [['1', 'n']];
        yield [['1', null]];
    }

    #[DataProvider('cancellations')]
    public function testCancellationDoesNotSendAnAction(array $inputs): void
    {
        $command = $this->command();
        $command->inputs = $inputs;
        $command->responses = [$this->package($this->tasks())];
        [$result] = $this->execute($command, 'stop');
        self::assertSame([['crontabInfo', []]], $command->calls);
        self::assertSame([], $result->result);
        self::assertSame('task selection cancelled', $result->msg);
    }

    public function testNonTerminalRequiresName(): void
    {
        $command = $this->command();
        $command->interactive = false;
        [$result] = $this->execute($command, 'resume');
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertStringContainsString('pass --taskName', $result->msg);
        self::assertSame([], $command->calls);
    }

    public static function changedTasks(): iterable
    {
        yield ['missing'];
        yield ['rule'];
        yield ['state'];
    }

    #[DataProvider('changedTasks')]
    public function testChangedSelectionAbortsAllActions(string $change): void
    {
        $current = $this->tasks();
        if ($change === 'missing') { unset($current['Cleanup']); }
        if ($change === 'rule') { $current['Cleanup']['taskRule'] = '* * * * *'; }
        if ($change === 'state') { $current['Cleanup']['isStop'] = 0; }
        $command = $this->command();
        $command->inputs = ['1,2', 'y'];
        $command->responses = [$this->package($this->tasks()), $this->package($current)];
        [$result] = $this->execute($command, 'stop');
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertCount(2, $command->calls);
        self::assertStringContainsString('disappeared or changed', $result->msg);
    }

    public function testEmptyListDoesNotPrompt(): void
    {
        $command = $this->command();
        $command->responses = [$this->package([])];
        [$result] = $this->execute($command, 'stop');
        self::assertSame('no crontab tasks available', $result->msg);
        self::assertSame([], $command->prompts);
    }

    public function testBridgeFailureDoesNotPrompt(): void
    {
        $command = $this->command();
        $command->responses = [$this->package(null, StatusEnum::CONNECT_ERROR)];
        [$result] = $this->execute($command, 'stop');
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertSame('Bridge failure', $result->msg);
        self::assertSame([], $command->prompts);
    }

    public function testBridgeExceptionIsReported(): void
    {
        $command = $this->command();
        $command->responses = [new \RuntimeException('connection error')];
        [$result] = $this->execute($command, 'stop');
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertSame('connection error', $result->msg);
    }

    public function testOneTaskFailureDoesNotHideOtherResults(): void
    {
        $command = $this->command();
        $command->inputs = ['1,2', 'y'];
        $command->responses = [$this->package($this->tasks()), $this->package($this->tasks()),
            $this->package(null, StatusEnum::COMMAND_EXEC_ERROR), $this->package()];
        [$result] = $this->execute($command, 'runJobNow');
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertSame('COMMAND_EXEC_ERROR', $result->result['Heartbeat']['status']);
        self::assertSame('SUCCESS', $result->result['Cleanup']['status']);
    }

    public function testExplicitEmptyNameIsRejected(): void
    {
        $command = $this->command();
        [$result] = $this->execute($command, 'stop', ['--taskName=']);
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_OPTION_VALIDATE_FAIL, $result->status);
        self::assertSame([], $command->calls);
    }

    public function testArrowMenuUsesTaskNamesAndRules(): void
    {
        $selector = new class('stop') extends CrontabSelector {
            public function draw(array $choices): int { return $this->render($choices, 0, [], 0); }
        };
        ob_start();
        try {
            $selector->draw([['taskName' => 'Heartbeat', 'taskRule' => '* * * * *', 'isStop' => 0]]);
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        self::assertStringContainsString('Select crontab tasks to stop:', $output);
        self::assertStringContainsString('Heartbeat', $output);
        self::assertStringContainsString('Rule * * * * *', $output);
        self::assertStringNotContainsString('PID', $output);
        self::assertStringNotContainsString('Memory', $output);
    }

    public function testShowUsesReadableEnglishListAndRetainsRawData(): void
    {
        $tasks = $this->tasks();
        $tasks['Heartbeat']['taskRunTimes'] = 12;
        $tasks['Heartbeat']['taskCurrentRunTime'] = 1700000000;
        $tasks['Cleanup']['taskRunTimes'] = 0;
        $tasks['Cleanup']['taskCurrentRunTime'] = 0;
        $command = $this->command();
        $command->responses = [$this->package($tasks)];
        [$result, $output] = $this->execute($command, 'show');
        self::assertSame(ExecStatusEnum::OK, $result->status);
        self::assertSame($tasks, $result->result);
        self::assertSame([['crontabInfo', []]], $command->calls);
        self::assertSame('', $output);
        self::assertSame([], $command->prompts);
        self::assertStringContainsString('Crontab tasks (total: 2, running: 1, stopped: 1)', $result->msg);
        self::assertStringContainsString('[1] Heartbeat', $result->msg);
        self::assertStringContainsString('[2] Cleanup', $result->msg);
        self::assertStringContainsString('Rule * * * * *   State running', $result->msg);
        self::assertStringContainsString('State stopped', $result->msg);
        self::assertStringContainsString('Next ' . date('Y-m-d H:i:s', 1700000000), $result->msg);
        self::assertStringContainsString('Runs 12   Last run ' . date('Y-m-d H:i:s', 1700000000), $result->msg);
        self::assertStringContainsString('Runs 0   Last run -', $result->msg);
        self::assertStringNotContainsString('┌', $result->msg);
        self::assertStringNotContainsString('│', $result->msg);
    }

    public function testShowEmptyList(): void
    {
        $command = $this->command();
        $command->responses = [$this->package([])];
        [$result] = $this->execute($command, 'show');
        self::assertSame([], $result->result);
        self::assertSame('No crontab tasks registered.', $result->msg);
    }

    public function testShowMissingMetricsUsesDashes(): void
    {
        $command = $this->command();
        $command->responses = [$this->package(['Heartbeat' => []])];
        [$result] = $this->execute($command, 'show');
        self::assertStringContainsString('Rule -   State running   Next -', $result->msg);
        self::assertStringContainsString('Runs 0   Last run -', $result->msg);
    }

    public function testShowPreservesBridgeError(): void
    {
        $command = $this->command();
        $command->responses = [$this->package(null, StatusEnum::CONNECT_ERROR)];
        [$result] = $this->execute($command, 'show');
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertSame('Bridge failure', $result->msg);
    }

    public function testShowReportsBridgeException(): void
    {
        $command = $this->command();
        $command->responses = [new \RuntimeException('connection error')];
        [$result] = $this->execute($command, 'show');
        self::assertSame(ExecStatusEnum::COMMAND_ACTION_EXEC_FAIL, $result->status);
        self::assertSame('connection error', $result->msg);
    }

}
