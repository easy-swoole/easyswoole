<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\EasySwoole\AbstractInterface\Log\LogLevelEnum;
use EasySwoole\EasySwoole\Test\Support\FilesystemTestCase;
use EasySwoole\EasySwoole\Utility\DefaultLogger;
use PHPUnit\Framework\Attributes\DataProvider;

final class DefaultLoggerTest extends FilesystemTestCase
{
    public static function prefixes(): iterable
    {
        yield 'with prefix' => ['dev', 'dev.log_'];
        yield 'null prefix' => [null, 'log_'];
        yield 'empty prefix' => ['', 'log_'];
    }

    #[DataProvider('prefixes')]
    public function testMonthlyFileAppendsEntries(?string $prefix, string $stem): void
    {
        $logger = new DefaultLogger($this->directory, $prefix);
        self::assertTrue($logger->log('first', LogLevelEnum::WARNING, 'app'));
        self::assertTrue($logger->log('second'));
        $files = glob($this->directory . '/' . $stem . '*.log');
        self::assertCount(1, $files);
        $contents = file_get_contents($files[0]);
        self::assertMatchesRegularExpression('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]\[WARNING\]\[app\]:first\n/', $contents);
        self::assertStringContainsString('[INFO][debug]:second' . "\n", $contents);
        self::assertSame(2, substr_count($contents, "\n"));
    }

    public static function consoleMessages(): iterable
    {
        yield 'ordinary text' => ['ordinary message', 'cli'];
        yield 'percentage' => ['100% complete', 'cli'];
        yield 'format placeholders' => ['literal %s %d %1$s', 'cli'];
        yield 'double percent' => ['literal %% remains unchanged', 'cli'];
        yield 'trailing percent' => ['progress: 50%', 'cli'];
        yield 'category placeholders' => ['message', 'category-%s-%d'];
    }

    #[DataProvider('consoleMessages')]
    public function testConsoleUsesDataAsText(string $message, string $category): void
    {
        $logger = new DefaultLogger($this->directory, null);
        ob_start();
        try {
            self::assertTrue($logger->console($message, LogLevelEnum::NOTICE, $category));
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }
        self::assertStringContainsString("[NOTICE][{$category}]:{$message}", $output);
        self::assertStringEndsWith("\n", $output);
    }
}
