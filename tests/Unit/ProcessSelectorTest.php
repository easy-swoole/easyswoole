<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\EasySwoole\Command\Selector\ProcessSelector;
use PHPUnit\Framework\Attributes\DataProvider;
use EasySwoole\EasySwoole\Test\Support\FakeTerminal;
use PHPUnit\Framework\TestCase;

final class ProcessSelectorTest extends TestCase
{
    private function selector(): ProcessSelector
    {
        return new class(new FakeTerminal()) extends ProcessSelector {
            public array $frames = [];
            public function __construct(public FakeTerminal $input) { parent::__construct($input); }
            protected function render(array $choices, int $cursor, array $selected, int $previousLines): int
            {
                $this->frames[] = [$cursor, array_keys($selected)];
                return 3;
            }
        };
    }

    private function rows(): array
    {
        return [['pid' => 101, 'name' => 'TaskWorker.0'], ['pid' => 202, 'name' => 'TaskWorker.1'], ['pid' => 303, 'name' => 'Bridge']];
    }

    public static function keys(): iterable
    {
        yield 'current item' => [['enter'], [0]];
        yield 'move down' => [['down', 'enter'], [1]];
        yield 'move up' => [['down', 'up', 'enter'], [0]];
        yield 'multi select' => [['toggle', 'down', 'toggle', 'enter'], [0, 1]];
        yield 'deselect' => [['toggle', 'down', 'toggle', 'up', 'toggle', 'enter'], [1]];
        yield 'select all' => [['all', 'enter'], [0, 1, 2]];
        yield 'toggle all off' => [['all', 'all', 'down', 'enter'], [1]];
        yield 'cancel' => [['toggle', 'cancel'], []];
        yield 'EOF' => [[null], []];
        yield 'cancel row' => [['up', 'enter'], []];
        yield 'wrap past cancel row' => [['up', 'down', 'enter'], [0]];
    }

    #[DataProvider('keys')]
    public function testSelectionAndTerminalRestoration(array $keys, array $expected): void
    {
        $selector = $this->selector();
        $selector->input->keys = $keys;
        ob_start();
        try {
            $result = $selector->select($this->rows());
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        self::assertSame($expected, array_keys($result));
        self::assertSame(['saved-mode'], end($selector->input->modes));
        self::assertStringContainsString("\033[?25h", $output);
    }

    public function testReadFailureStillRestoresTerminal(): void
    {
        $selector = $this->selector();
        $selector->input->throwOnRead = true;
        ob_start();
        try {
            try {
                $selector->select($this->rows());
                self::fail('Read failure should propagate');
            } catch (\RuntimeException $error) {
                self::assertSame('Read failed', $error->getMessage());
            }
            self::assertSame(['saved-mode'], end($selector->input->modes));
            self::assertStringContainsString("\033[?25h", ob_get_contents());
        } finally {
            ob_end_clean();
        }
    }

    public function testUnsupportedTerminalRequestsNumberedFallback(): void
    {
        $selector = $this->selector();
        $selector->input->supported = false;
        self::assertNull($selector->select($this->rows()));
        self::assertSame([], $selector->input->modes);
    }

    public function testRawModeFailureRestoresAndRequestsFallback(): void
    {
        $selector = $this->selector();
        $selector->input->failRawMode = true;
        self::assertNull($selector->select($this->rows()));
        self::assertSame(['saved-mode'], end($selector->input->modes));
    }

    public function testLabelDoesNotInterpretTerminalControlCharacters(): void
    {
        self::assertSame('Task?[2JWorker   PID 101', ProcessSelector::label(['pid' => 101, 'name' => "Task\033[2JWorker"]));
    }

    public static function memoryValues(): iterable
    {
        yield 'zero' => [0, '0 B'];
        yield 'bytes' => [512, '512 B'];
        yield 'kilobytes' => [1536, '1.5 KB'];
        yield 'megabytes' => [4194304, '4 MB'];
        yield 'gigabytes' => [1073741824, '1 GB'];
        yield 'missing' => [null, '-'];
        yield 'negative' => [-1, '-'];
    }

    #[DataProvider('memoryValues')]
    public function testMemoryAndWorkerStartupDetails(mixed $memory, string $formatted): void
    {
        $started = 1700000000;
        $details = ProcessSelector::details(['memoryUsage' => $memory, 'startUpTime' => $started]);
        self::assertSame('Memory ' . $formatted . '   processUpTime ' . date('Y-m-d H:i:s', $started), $details);
    }

    public function testMissingStartupTimeIsDisplayedAsUnknown(): void
    {
        self::assertSame('Memory -   processUpTime -', ProcessSelector::details([]));
        self::assertStringEndsWith('processUpTime -', ProcessSelector::details(['startUpTime' => 0]));
    }

    public function testArrowMenuShowsDetailsOnASeparateLine(): void
    {
        $selector = new class extends ProcessSelector {
            public function draw(array $choices): int { return $this->render($choices, 0, [], 0); }
        };
        $started = 1700000000;
        ob_start();
        try {
            $lines = $selector->draw([['pid' => 101, 'name' => 'TaskWorker.0', 'memoryUsage' => 4194304, 'startUpTime' => $started]]);
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        self::assertSame(6, $lines);
        self::assertStringContainsString('TaskWorker.0   PID 101', $output);
        self::assertStringContainsString('Memory 4 MB', $output);
        self::assertStringContainsString('processUpTime ' . date('Y-m-d H:i:s', $started), $output);
    }

}
