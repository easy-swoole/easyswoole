<?php

declare(strict_types=1);

namespace EasySwoole\EasySwoole\Test\Unit;

use EasySwoole\EasySwoole\AbstractInterface\Log\LoggerInterface;
use EasySwoole\EasySwoole\AbstractInterface\Log\LogLevelEnum;
use EasySwoole\EasySwoole\Logger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LoggerTest extends TestCase
{
    public static function levels(): iterable
    {
        yield ['info', LogLevelEnum::INFO];
        yield ['notice', LogLevelEnum::NOTICE];
        yield ['warning', LogLevelEnum::WARNING];
        yield ['error', LogLevelEnum::ERROR];
    }

    #[DataProvider('levels')]
    public function testConvenienceMethodsForwardLevelAndCategory(string $method, LogLevelEnum $level): void
    {
        $handler = $this->createMock(LoggerInterface::class);
        $handler->expects(self::once())->method('console')->with('message', $level, 'app')->willReturn(true);
        $handler->expects(self::once())->method('log')->with('message', $level, 'app')->willReturn(true);
        self::assertTrue((new Logger($handler))->{$method}('message', 'app'));
    }

    public function testLevelFilterPreventsBothOutputs(): void
    {
        $handler = $this->createMock(LoggerInterface::class);
        $handler->expects(self::never())->method('console');
        $handler->expects(self::never())->method('log');
        $logger = new Logger($handler);
        self::assertSame([LogLevelEnum::ERROR], $logger->logLevels([LogLevelEnum::ERROR]));
        self::assertFalse($logger->info('filtered'));
    }

    public function testIgnoredCategoryPreventsBothOutputs(): void
    {
        $handler = $this->createMock(LoggerInterface::class);
        $handler->expects(self::never())->method('console');
        $handler->expects(self::never())->method('log');
        $logger = new Logger($handler);
        self::assertSame($logger, $logger->ignoreCategory(['noise']));
        self::assertSame(['noise'], $logger->ignoreCategory());
        self::assertFalse($logger->error('filtered', 'noise'));
    }

    public function testDisabledConsoleStillWritesLogAndReturnsHandlerResult(): void
    {
        $handler = $this->createMock(LoggerInterface::class);
        $handler->expects(self::never())->method('console');
        $handler->expects(self::once())->method('log')->with('message', LogLevelEnum::INFO, null)->willReturn(false);
        $logger = new Logger($handler);
        self::assertTrue($logger->displayConsole());
        self::assertSame($logger, $logger->displayConsole(false));
        self::assertFalse($logger->displayConsole());
        self::assertFalse($logger->info('message'));
        self::assertFalse($logger->console('message'));
    }

    public function testEmptyLevelListAllowsAllLevels(): void
    {
        $handler = $this->createMock(LoggerInterface::class);
        $handler->expects(self::once())->method('log')->willReturn(true);
        $logger = new Logger($handler);
        $logger->displayConsole(false);
        $logger->logLevels([LogLevelEnum::ERROR]);
        $logger->logLevels([]);
        self::assertTrue($logger->info('allowed'));
    }
}
