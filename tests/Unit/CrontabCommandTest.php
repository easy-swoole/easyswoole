<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\Bridge\Package;
use EasySwoole\Bridge\StatusEnum;
use EasySwoole\Command\Result\CommandResult as Result;
use EasySwoole\Command\Application;
use EasySwoole\Command\Input\Input;
use EasySwoole\Command\Console\Console;
use EasySwoole\EasySwoole\Test\Support\TestConsole;
use EasySwoole\EasySwoole\Command\Selector\CrontabSelector;
use EasySwoole\EasySwoole\Command\RuntimeClient as Crontab;
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
            public function call(string $action, array $args = []): Package
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
        $class = 'EasySwoole\\EasySwoole\\Command\\DefaultCommand\\Crontab' . ucfirst($action) . 'Action';
        $app = new Application();
        $app->addCommand(new \EasySwoole\EasySwoole\Command\DefaultCommand\CrontabCommand([$action => new $class($command)]));
        ob_start();
        try {
            $result = $app->dispatch(array_merge(['crontab', $action], $options), new TestConsole($command));
            $output = ob_get_contents();
        } finally { ob_end_clean(); }
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
        self::assertSame(0, $result->exitCode);
        self::assertStringContainsString('Select crontab tasks', $output);
        self::assertStringContainsString('Rule * * * * *', $output);
        self::assertStringContainsString('State stopped', $output);
        self::assertStringContainsString('Invalid selection', $output);
        self::assertStringContainsString('Heartbeat, Cleanup', end($command->prompts));
        self::assertSame(['Heartbeat', 'Cleanup'], array_keys($result->data));
    }

    #[DataProvider('actions')]
    public function testExplicitNameWorksWithoutTerminalOrExtraQueries(string $action, string $bridgeAction): void
    {
        $command = $this->command();
        $command->interactive = false;
        $command->responses = [$this->package()];
        [$result, $output] = $this->execute($command, $action, ['--taskName=Heartbeat']);
        self::assertSame([[$bridgeAction, ['taskName' => 'Heartbeat']]], $command->calls);
        self::assertSame('done', $result->message);
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
        self::assertSame([], $result->data);
        self::assertSame('task selection cancelled', $result->message);
    }

    public function testNonTerminalRequiresName(): void
    {
        $command = $this->command();
        $command->interactive = false;
        [$result] = $this->execute($command, 'resume');
        self::assertSame(1, $result->exitCode);
        self::assertStringContainsString('pass --taskName', $result->message);
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
        self::assertSame(1, $result->exitCode);
        self::assertCount(2, $command->calls);
        self::assertStringContainsString('disappeared or changed', $result->message);
    }

    public function testEmptyListDoesNotPrompt(): void
    {
        $command = $this->command();
        $command->responses = [$this->package([])];
        [$result] = $this->execute($command, 'stop');
        self::assertSame('no crontab tasks available', $result->message);
        self::assertSame([], $command->prompts);
    }

    public function testBridgeFailureDoesNotPrompt(): void
    {
        $command = $this->command();
        $command->responses = [$this->package(null, StatusEnum::CONNECT_ERROR)];
        [$result] = $this->execute($command, 'stop');
        self::assertSame(1, $result->exitCode);
        self::assertSame('Bridge failure', $result->message);
        self::assertSame([], $command->prompts);
    }

    public function testBridgeExceptionIsReported(): void
    {
        $command = $this->command();
        $command->responses = [new \RuntimeException('connection error')];
        [$result] = $this->execute($command, 'stop');
        self::assertSame(1, $result->exitCode);
        self::assertSame('connection error', $result->message);
    }

    public function testOneTaskFailureDoesNotHideOtherResults(): void
    {
        $command = $this->command();
        $command->inputs = ['1,2', 'y'];
        $command->responses = [$this->package($this->tasks()), $this->package($this->tasks()),
            $this->package(null, StatusEnum::COMMAND_EXEC_ERROR), $this->package()];
        [$result] = $this->execute($command, 'runJobNow');
        self::assertSame(1, $result->exitCode);
        self::assertSame('COMMAND_EXEC_ERROR', $result->data['Heartbeat']['status']);
        self::assertSame('SUCCESS', $result->data['Cleanup']['status']);
    }

    public function testExplicitEmptyNameIsRejected(): void
    {
        $command = $this->command();
        [$result] = $this->execute($command, 'stop', ['--taskName=']);
        self::assertSame(2, $result->exitCode);
        self::assertSame([], $command->calls);
    }

    public function testChoicesUseTaskNamesAndRules(): void
    {
        $task = ['taskName' => 'Heartbeat', 'taskRule' => '* * * * *', 'isStop' => 0];
        self::assertSame('Heartbeat', CrontabSelector::label($task));
        self::assertStringContainsString('Rule * * * * *', CrontabSelector::details($task));
    }

    public function testShowUsesReadableEnglishListAndRetainsRawData(): void
    {
        $tasks = $this->tasks();
        $tasks['Heartbeat']['taskRunTimes'] = 12;
        $tasks['Heartbeat']['taskLastRunTime'] = 1700000000;
        $tasks['Cleanup']['taskRunTimes'] = 0;
        $tasks['Cleanup']['taskLastRunTime'] = 0;
        $command = $this->command();
        $command->responses = [$this->package($tasks)];
        [$result, $output] = $this->execute($command, 'show');
        self::assertSame(0, $result->exitCode);
        self::assertSame($tasks, $result->data);
        self::assertSame([['crontabInfo', []]], $command->calls);
        self::assertSame('', $output);
        self::assertSame([], $command->prompts);
        self::assertStringContainsString('Crontab tasks (total: 2, running: 1, stopped: 1)', $result->message);
        self::assertStringContainsString('[1] Heartbeat', $result->message);
        self::assertStringContainsString('[2] Cleanup', $result->message);
        self::assertStringContainsString('Rule * * * * *   State running', $result->message);
        self::assertStringContainsString('State stopped', $result->message);
        self::assertStringContainsString('Next ' . date('Y-m-d H:i:s', 1700000000), $result->message);
        self::assertStringContainsString('Runs 12   Last run ' . date('Y-m-d H:i:s', 1700000000), $result->message);
        self::assertStringContainsString('Runs 0   Last run -', $result->message);
        self::assertStringNotContainsString('Last error', $result->message);
        self::assertStringNotContainsString('┌', $result->message);
        self::assertStringNotContainsString('│', $result->message);
    }

    public function testShowDisplaysLastErrorAndPreservesRawData(): void
    {
        $tasks = $this->tasks();
        $tasks['Heartbeat'] += ['taskLastRunTime' => 1700000100,
            'lastError' => "Failed at 50%\n\e[31m", 'lastErrorTime' => 1700000000];
        $tasks['Cleanup'] += ['lastError' => '', 'lastErrorTime' => 0];
        $command = $this->command();
        $command->responses = [$this->package($tasks)];
        [$result] = $this->execute($command, 'show');
        self::assertSame(0, $result->exitCode);
        self::assertSame($tasks, $result->data);
        self::assertStringContainsString('Last run ' . date('Y-m-d H:i:s', 1700000100), $result->message);
        self::assertStringContainsString('Last error Failed at 50%??[31m', $result->message);
        self::assertStringContainsString('Last error time ' . date('Y-m-d H:i:s', 1700000000), $result->message);
        self::assertSame(1, substr_count($result->message, 'Last error time'));
        self::assertStringNotContainsString("\e", $result->message);
    }

    public function testShowDisplaysErrorTimeEvenWhenExceptionMessageIsEmpty(): void
    {
        $command = $this->command();
        $command->responses = [$this->package(['Heartbeat' => ['lastError' => '', 'lastErrorTime' => 1700000000]])];
        [$result] = $this->execute($command, 'show');
        self::assertStringContainsString('Last error -', $result->message);
        self::assertStringContainsString('Last error time ' . date('Y-m-d H:i:s', 1700000000), $result->message);
    }

    public function testShowEmptyList(): void
    {
        $command = $this->command();
        $command->responses = [$this->package([])];
        [$result] = $this->execute($command, 'show');
        self::assertSame([], $result->data);
        self::assertSame('No crontab tasks registered.', $result->message);
    }

    public function testShowMissingMetricsUsesDashes(): void
    {
        $command = $this->command();
        $command->responses = [$this->package(['Heartbeat' => []])];
        [$result] = $this->execute($command, 'show');
        self::assertStringContainsString('Rule -   State running   Next -', $result->message);
        self::assertStringContainsString('Runs 0   Last run -', $result->message);
        self::assertStringNotContainsString('Last error', $result->message);
    }

    public function testShowPreservesBridgeError(): void
    {
        $command = $this->command();
        $command->responses = [$this->package(null, StatusEnum::CONNECT_ERROR)];
        [$result] = $this->execute($command, 'show');
        self::assertSame(1, $result->exitCode);
        self::assertSame('Bridge failure', $result->message);
    }

    public function testShowReportsBridgeException(): void
    {
        $command = $this->command();
        $command->responses = [new \RuntimeException('connection error')];
        [$result] = $this->execute($command, 'show');
        self::assertSame(1, $result->exitCode);
        self::assertSame('connection error', $result->message);
    }

}
