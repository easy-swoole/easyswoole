<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\EasySwoole\Command\Selector\ProcessSelector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProcessSelectorTest extends TestCase
{
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

}
